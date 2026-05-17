<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 2. Drop existing foreign keys first in a separate block!
        Schema::table('property_images', function (Blueprint $table) {
            $conn = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = array_map(function($key) {
                return $key->getName();
            }, $conn->listTableForeignKeys('property_images'));

            if (in_array('property_images_uploaded_by_foreign', $foreignKeys)) {
                $table->dropForeign(['uploaded_by']);
            }
            if (in_array('property_images_property_id_foreign', $foreignKeys)) {
                $table->dropForeign(['property_id']);
            }
            if (in_array('property_images_apartment_id_foreign', $foreignKeys)) {
                $table->dropForeign(['apartment_id']);
            }
        });

        // 3. Change columns to match referenced unsignedBigInteger types
        Schema::table('property_images', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->change();
            $table->unsignedBigInteger('apartment_id')->nullable()->change();
            $table->unsignedBigInteger('uploaded_by')->change();
        });

        // 4. Re-add foreign keys referencing business IDs
        Schema::table('property_images', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('property_id')->on('properties')->onDelete('cascade');
            $table->foreign('apartment_id')->references('apartment_id')->on('apartments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('property_images', function (Blueprint $table) {
            try { $table->dropForeign(['uploaded_by']); } catch (\Exception $e) {}
            try { $table->dropForeign(['property_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['apartment_id']); } catch (\Exception $e) {}
        });

        Schema::table('property_images', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
        });
    }
};
