<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_messages')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                if (! Schema::hasColumn('contact_messages', 'reply_subject')) {
                    $table->string('reply_subject')->nullable()->after('is_read');
                }
                if (! Schema::hasColumn('contact_messages', 'reply_message')) {
                    $table->text('reply_message')->nullable()->after('reply_subject');
                }
                if (! Schema::hasColumn('contact_messages', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('reply_message');
                }
                if (! Schema::hasColumn('contact_messages', 'replied_by')) {
                    $table->unsignedBigInteger('replied_by')->nullable()->after('replied_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contact_messages')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $cols = collect(['reply_subject', 'reply_message', 'replied_at', 'replied_by'])
                    ->filter(fn ($col) => Schema::hasColumn('contact_messages', $col))
                    ->values()
                    ->all();

                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

