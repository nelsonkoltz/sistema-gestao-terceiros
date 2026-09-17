<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('aplicacao', 20)->default('ambos');
            $table->boolean('obrigatorio')->default(true);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->after('empresa_id')->constrained('tipos_documento')->nullOnDelete();
            $table->string('status', 30)->default('Pendente')->after('caminho_arquivo');
            $table->date('validade_ate')->nullable()->after('status');
            $table->text('observacao_analise')->nullable()->after('validade_ate');
            $table->foreignId('analisado_por')->nullable()->after('observacao_analise')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('analisado_em')->nullable()->after('analisado_por');
            $table->foreignId('documento_anterior_id')->nullable()->after('analisado_em')->constrained('documentos')->nullOnDelete();
        });

        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->after('funcionario_id')->constrained('tipos_documento')->nullOnDelete();
            $table->string('status', 30)->default('Pendente')->after('tamanho');
            $table->date('validade_ate')->nullable()->after('status');
            $table->text('observacao_analise')->nullable()->after('validade_ate');
            $table->foreignId('analisado_por')->nullable()->after('observacao_analise')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('analisado_em')->nullable()->after('analisado_por');
            $table->foreignId('documento_anterior_id')->nullable()->after('analisado_em')->constrained('funcionario_documentos')->nullOnDelete();
        });

        DB::table('configuracoes')->insert([
            'chave' => 'job_validade_documentos_ativo',
            'valor' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['documentos', 'funcionario_documentos'] as $tabela) {
            DB::table($tabela)->select('id', 'created_at')->orderBy('id')->each(function ($documento) use ($tabela) {
                DB::table($tabela)->where('id', $documento->id)->update([
                    'validade_ate' => \Carbon\Carbon::parse($documento->created_at)->addMonthsNoOverflow(6)->toDateString(),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('funcionario_documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('documento_anterior_id');
            $table->dropConstrainedForeignId('analisado_por');
            $table->dropConstrainedForeignId('tipo_documento_id');
            $table->dropColumn(['status', 'validade_ate', 'observacao_analise', 'analisado_em']);
        });
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('documento_anterior_id');
            $table->dropConstrainedForeignId('analisado_por');
            $table->dropConstrainedForeignId('tipo_documento_id');
            $table->dropColumn(['status', 'validade_ate', 'observacao_analise', 'analisado_em']);
        });
        Schema::dropIfExists('configuracoes');
        Schema::dropIfExists('tipos_documento');
    }
};
