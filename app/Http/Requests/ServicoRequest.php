<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicoRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'descricao' => trim((string) $this->descricao),
        ]);
    }

    public function authorize()
    {
        // Se futuramente usar Policy, pode trocar por $this->user()->can(...)
        return true;
    }

    public function rules()
    {
        return [
            'descricao' => ['required', 'string', 'max:1000'],

            'empresa_id' => [
                'required',
                $this->isMethod('post')
                    ? Rule::exists('empresas', 'id')->where(fn ($query) => $query->where('ativo', true))
                    : 'exists:empresas,id',
            ],

            'setor_id' => ['required', 'exists:setores,id'],

            'vai_almocar' => ['required', 'boolean'],

            'status' => ['required', 'in:Agendado,Em Andamento,Finalizado,Cancelado'],


            'data_servico' => array_filter([
                'required',
                'date',
                $this->isMethod('post') ? 'after_or_equal:today' : null,
            ]),

            'data_conclusao' => ['nullable', 'date', 'after_or_equal:data_servico'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ];
    }

    /**
     * Opcional: mensagens personalizadas
     */
    public function messages()
    {
        return [
            'descricao.required' => 'A descrição do serviço é obrigatória.',
            'descricao.max' => 'A descrição pode ter no máximo 1000 caracteres.',

            'empresa_id.required' => 'Selecione uma empresa.',
            'empresa_id.exists' => 'Empresa inválida.',

            'setor_id.required' => 'Selecione um setor.',
            'setor_id.exists' => 'Setor inválido.',

            'vai_almocar.required' => 'Informe se o terceiro irá almoçar.',
            'vai_almocar.boolean' => 'Valor inválido para almoço.',

            'status.required' => 'Informe o status do serviço.',
            'status.in' => 'Status inválido.',

            'data_servico.required' => 'Informe a data do serviço.',
            'data_servico.date' => 'Data do serviço inválida.',
            'data_servico.after_or_equal' => 'A data do serviço não pode estar no passado.',

            'data_conclusao.date' => 'Data de conclusão inválida.',
            'data_conclusao.after_or_equal' => 'A conclusão não pode ser anterior à data do serviço.',
            'hora_inicio.required' => 'Informe o horário inicial.',
            'hora_fim.required' => 'Informe o horário final.',
            'hora_fim.after' => 'O horário final deve ser posterior ao horário inicial.',
        ];
    }
}
