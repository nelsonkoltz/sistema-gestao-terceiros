<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicoRequest extends FormRequest
{
    public function authorize()
    {
        // Se futuramente usar Policy, pode trocar por $this->user()->can(...)
        return true;
    }

    public function rules()
    {
        return [
            'descricao' => ['required', 'string', 'max:1000'],

            'empresa_id' => ['required', 'exists:empresas,id'],

            'setor_id' => ['required', 'exists:setores,id'],

            'vai_almocar' => ['required', 'boolean'],

            'status' => ['required', 'in:Pendente,Em Andamento,Finalizado,Cancelado'],


            'data_servico' => ['required', 'date'],

            'data_conclusao' => ['nullable', 'date'],
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

            'data_conclusao.date' => 'Data de conclusão inválida.',
        ];
    }
}
