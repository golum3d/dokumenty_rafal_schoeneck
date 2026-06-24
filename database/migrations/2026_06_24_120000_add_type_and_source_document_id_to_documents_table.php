<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('type', ['document', 'change', 'repeal'])
                ->default('document')
                ->after('status');
            $table->foreignId('source_document_id')
                ->nullable()
                ->after('type')
                ->constrained('documents')
                ->nullOnDelete();
        });

        DB::table('documents')
            ->whereNull('type')
            ->update(['type' => 'document']);
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_document_id');
            $table->dropColumn('type');
        });
    }
};
