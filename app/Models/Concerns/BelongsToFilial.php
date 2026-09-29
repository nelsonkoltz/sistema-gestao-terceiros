<?php

namespace App\Models\Concerns;

use App\Models\Filial;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToFilial
{
    public static function bootBelongsToFilial(): void
    {
        static::addGlobalScope('filial', function (Builder $query) {
            if (request()->attributes->has('filial_ativa_id')) {
                $query->where($query->getModel()->qualifyColumn('filial_id'), request()->attributes->get('filial_ativa_id'));
            }
        });

        static::creating(function ($model) {
            if (request()->attributes->has('filial_ativa_id')) {
                $model->filial_id = request()->attributes->get('filial_ativa_id');
                abort_unless($model->filial_id, 403, 'Selecione uma filial ativa.');
            } elseif (!$model->filial_id) {
                $model->filial_id = Filial::where('codigo', 'MATRIZ')->value('id');
            }
        });
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
