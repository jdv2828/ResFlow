<?php

namespace App\Presenters;

class ActivityLogPresenter
{
    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'recurso.created' => 'Recurso creado',
            'recurso.deleted' => 'Recurso eliminado',
            'recurso.activated' => 'Recurso activado',
            'recurso.deactivated' => 'Recurso desactivado',
            'ticket.created' => 'Ticket creado',
            'ticket.anulado' => 'Ticket anulado',
            'ticket.deleted' => 'Ticket anulado',
            'ticket.updated' => 'Ticket modificado',
            'ticket.consumed' => 'Ticket consumido',
            'ticket.expired' => 'Ticket vencido',
            'ticket.marked_as_in_process' => 'Ticket marcado en proceso',
            'ticket.marked_as_pending' => 'Ticket marcado pendiente',
            default => str($action)->replace('.', ' ')->headline()->toString(),
        };
    }

    public static function actionBadgeClasses(string $action): string
    {
        $prefix = str($action)->before('.')->toString();

        return match ($prefix) {
            'ticket' => 'bg-blue-100 text-blue-800',
            'recurso' => 'bg-green-100 text-green-800',
            'user' => 'bg-purple-100 text-purple-800',
            'vehiculo' => 'bg-yellow-100 text-yellow-800',
            'personal' => 'bg-orange-100 text-orange-800',
            'centro_costo' => 'bg-gray-200 text-gray-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    public static function formatData(array $data): array
    {
        return collect($data)
            ->mapWithKeys(function ($value, $key) {
                if ($key === 'recursos_consumidos' && is_array($value)) {
                    return [
                        'Recursos Consumidos' => collect($value)
                            ->map(fn (array $item) => [
                                'recurso_id' => $item['recurso_id'] ?? null,
                                'numero_factura' => $item['numero_factura'] ?? 'N/A',
                                'litros' => number_format((float) ($item['litros'] ?? 0), 2, ',', '.') . ' L',
                            ])
                            ->all(),
                    ];
                }

                return [
                    str($key)->replace('_', ' ')->headline()->toString() => is_array($value)
                        ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : (is_bool($value) ? ($value ? 'Sí' : 'No') : ($value ?? 'N/A')),
                ];
            })
            ->all();
    }
}
