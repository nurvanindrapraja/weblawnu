<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('ticket_number', 20)->nullable()->after('id');
            $table->text('reply')->nullable()->after('admin_note');
            $table->timestamp('replied_at')->nullable()->after('handled_at');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->unique('ticket_number');
        });

        // Backfill nomor tiket untuk pesan yang sudah ada.
        $rows = DB::table('contact_messages')->whereNull('ticket_number')->orderBy('id')->get(['id']);

        foreach ($rows as $row) {
            do {
                $ticket = 'TKT-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            } while (DB::table('contact_messages')->where('ticket_number', $ticket)->exists());

            DB::table('contact_messages')->where('id', $row->id)->update(['ticket_number' => $ticket]);
        }
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropUnique(['ticket_number']);
            $table->dropColumn(['ticket_number', 'reply', 'replied_at']);
        });
    }
};
