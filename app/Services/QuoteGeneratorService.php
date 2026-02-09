<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\QuoteProfile;
use Illuminate\Support\Facades\Http;

final class QuoteGeneratorService
{
    private const PERIODO_LABELS = [
        'mes' => ['singular' => 'mes', 'plural' => 'meses', 'periodo_plural' => 'mensuales'],
        'semana' => ['singular' => 'semana', 'plural' => 'semanas', 'periodo_plural' => 'semanales'],
    ];

    public function __construct(
        private readonly string $origin,
        private readonly int $distanceBaseMeters,
        private readonly float $pricePerKm,
        private readonly ?string $googleMapsApiKey = null,
    ) {}

    public static function fromConfig(): self
    {
        $config = config('quote', []);

        return new self(
            $config['origin'] ?? '25.694905,-100.3520877',
            $config['distance_base_meters'] ?? 30000,
            (float) ($config['price_per_km'] ?? 30),
            $config['google_maps_api_key'] ?? null,
        );
    }

    /**
     * Generate quote string for product + location + periodo (replicates legacy cotizarString).
     */
    public function generate(Product $product, string $location, string $periodo = 'mes'): string
    {
        $product->loadMissing(['material', 'paymentPlan', 'quoteProfile.quoteTemplate']);

        $tarifaEnvio = $this->computeTarifaEnvio(
            (float) $product->price,
            $product->trips_required ?? 1,
            $location,
        );

        $profile = $product->quoteProfile
            ?? QuoteProfile::query()->where('is_default', true)->with('quoteTemplate')->first()
            ?? QuoteProfile::query()->where('slug', 'speech-1')->with('quoteTemplate')->first();
        $slug = $profile?->slug ?? 'speech-1';

        $vars = $this->buildVariables($product, $tarifaEnvio, $periodo, $slug);

        $template = $profile?->quoteTemplate?->content ?? $this->fallbackTemplate($slug);
        return $this->replacePlaceholders($template, $vars);
    }

    private function computeTarifaEnvio(float $productPrice, int $tripsRequired, string $location): float
    {
        $distancia = $this->getDistanceMeters($location);
        if ($distancia === null || $distancia <= $this->distanceBaseMeters) {
            return 0.0;
        }

        return (float) (ceil($distancia / 1000) * $this->pricePerKm * $tripsRequired);
    }

    private function getDistanceMeters(string $location): ?int
    {
        if (! $this->googleMapsApiKey || trim($location) === '') {
            return null;
        }

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query([
            'origins' => $this->origin,
            'destinations' => $location,
            'key' => $this->googleMapsApiKey,
        ]);

        $response = Http::timeout(5)->get($url);
        $data = $response->json();

        return $data['rows'][0]['elements'][0]['distance']['value'] ?? null;
    }

    private function buildVariables(Product $product, float $tarifaEnvio, string $periodo, string $slug): array
    {
        $per = self::PERIODO_LABELS[$periodo] ?? self::PERIODO_LABELS['mes'];
        $price = (float) $product->price;
        $precioReal = $price + $tarifaEnvio;

        $vars = [
            'product.name' => $product->name ?? '',
            'product.material' => $product->material?->name ?? '',
            'product.included' => $product->included ?? '',
            'product.price' => $this->formatCurrency($price),
            'product.trips_required' => (string) ($product->trips_required ?? 1),
            'quote.tarifa_envio' => $this->formatCurrency($tarifaEnvio),
            'quote.precio_real' => $this->formatCurrency($precioReal),
            'periodo.plural' => $per['plural'],
            'periodo.singular' => $per['singular'],
            'periodo.periodo_plural' => $per['periodo_plural'],
            'quote.string_pago_inicial' => '',
            'quote.restante_string' => '',
        ];

        if ($slug === 'speech-1') {
            $this->applySpeech1($vars, $price, $tarifaEnvio, $periodo);
        } else {
            $this->applySpeech2Or3($vars, $product, $tarifaEnvio, $periodo, $slug);
        }

        return $vars;
    }

    private function applySpeech1(array &$vars, float $price, float $tarifaEnvio, string $periodo): void
    {
        $pago3meses = ($price + $tarifaEnvio) / 3;
        $abono = ($price + $tarifaEnvio) / 12;
        $pagosTotales = 12;
        $stringPagoInicial = '';

        if ($periodo === 'semana') {
            $enganche = $abono;
            $pagosTotales = (int) ceil(11 * 4.3333);
            $abono = ($abono * 11) / $pagosTotales;
            $stringPagoInicial = ' con un único pago inicial de $' . $this->formatCurrency($enganche);
        }

        $vars['quote.pagos_totales'] = (string) $pagosTotales;
        $vars['quote.abono'] = $this->formatCurrency($abono);
        $vars['quote.pago3meses'] = $this->formatCurrency($pago3meses);
        $vars['quote.string_pago_inicial'] = $stringPagoInicial;
        $vars['quote.precio_lista'] = $this->formatCurrency(ceil(($price + $tarifaEnvio) / 0.9));
        $vars['quote.pagos_enganche'] = '';
        $vars['quote.pagos_restantes'] = '';
        $vars['quote.abono_enganche'] = '';
        $vars['quote.primer_pago'] = '';
    }

    private function applySpeech2Or3(array &$vars, Product $product, float $tarifaEnvio, string $periodo, string $slug): void
    {
        $plan = $product->paymentPlan;
        $price = (float) $product->price;

        if ($slug === 'speech-2') {
            $vars['quote.precio_lista'] = $this->formatCurrency(ceil(($price + $tarifaEnvio) / 0.9));
        }

        $pagosTotales = 12;
        $pagosEnganche = 0;
        $mensualidadEnganche = 0.0;
        $mensualidad = 0.0;
        $pagosRestantes = 0;

        if ($plan) {
            $pagosTotales = (int) $plan->payments;
            $pagosEnganche = (int) ($plan->down_payments ?? 0);
            $mensualidadEnganche = (float) $plan->amount;
            $mensualidad = (float) ($plan->down_amount ?? 0);

            if ($pagosEnganche <= $pagosTotales && $pagosTotales > 0) {
                $pagosRestantes = $pagosTotales - $pagosEnganche;
                $montoRestante = ceil($price - ($pagosEnganche * $mensualidadEnganche));
                $mensualidad = $pagosRestantes > 0 ? $montoRestante / $pagosRestantes : 0.0;
                if ($plan->down_amount !== null) {
                    $mensualidad = (float) $plan->down_amount;
                }
                if ($tarifaEnvio > 0) {
                    $mensualidadEnvio = $tarifaEnvio / $pagosTotales;
                    $mensualidadEnganche += $mensualidadEnvio;
                    $mensualidad += $mensualidadEnvio;
                }
            }
        } else {
            $pagosEnganche = 1;
            $pagosRestantes = $pagosTotales - 1;
            $mensualidadEnganche = $price / $pagosTotales;
            $mensualidad = $price / $pagosTotales;
        }

        $abonoEnganche = (int) ceil($mensualidadEnganche);
        $abono = (int) ceil($mensualidad);
        $primerPago = $abonoEnganche;
        $stringPagoInicial = '';
        $pagosRestantesDisplay = $pagosRestantes;

        if ($periodo === 'semana') {
            $montoEnganche = ($pagosEnganche - 1) * $abonoEnganche;
            $montoRestante = $pagosRestantes * $abono;
            $pagosEngancheSem = (int) ceil(($pagosEnganche - 1) * 4.3333);
            $pagosRestantesSem = (int) ceil($pagosRestantes * 4.3333);
            $pagosTotales = $pagosEngancheSem + $pagosRestantesSem;
            $abonoEnganche = (int) ceil($montoEnganche / max(1, $pagosEngancheSem));
            $abono = (int) ceil($montoRestante / max(1, $pagosRestantesSem));
            $stringPagoInicial = ' con un único pago inicial de $' . $this->formatCurrency($primerPago);
            $pagosRestantesDisplay = $pagosRestantesSem;
        }

        $vars['quote.pagos_totales'] = (string) $pagosTotales;
        $vars['quote.abono'] = $this->formatCurrency($abono);
        $vars['quote.pago3meses'] = $this->formatCurrency(($price + $tarifaEnvio) / 3);
        $vars['quote.pagos_enganche'] = (string) $pagosEnganche;
        $vars['quote.pagos_restantes'] = (string) $pagosRestantesDisplay;
        $vars['quote.abono_enganche'] = $this->formatCurrency($abonoEnganche);
        $vars['quote.primer_pago'] = $this->formatCurrency($primerPago);
        $vars['quote.string_pago_inicial'] = $stringPagoInicial;

        if ($slug === 'speech-2') {
            if ($pagosEnganche < $pagosTotales && $pagosRestantes > 0) {
                $vars['quote.restante_string'] = ' y los ' . $pagosRestantesDisplay . ' restantes de  $' . $this->formatCurrency($abono) . $stringPagoInicial;
            }
            // Si no aplica, restante_string queda en '' (mismo flujo que legacy)
        } else {
            // Speech 3: legacy siempre define restante_string (líneas 143-144)
            $vars['quote.restante_string'] = ' y ' . $pagosRestantesDisplay . ' pagos aún más bajos de $' . $this->formatCurrency($abono) . $stringPagoInicial;
        }
    }

    private function replacePlaceholders(string $template, array $vars): string
    {
        $result = $template;
        foreach ($vars as $key => $value) {
            $result = str_replace('{{' . $key . '}}', (string) $value, $result);
        }

        return $result;
    }

    private function formatCurrency(float $value): string
    {
        return number_format(round($value, 2), 2, '.', ',');
    }

    private function fallbackTemplate(string $slug): string
    {
        return 'Cotización para {{product.name}} ({{product.material}}). Precio: ${{quote.precio_real}}. ' .
            '{{quote.pagos_totales}} pagos {{periodo.plural}} de ${{quote.abono}}. {{product.included}}';
    }
}
