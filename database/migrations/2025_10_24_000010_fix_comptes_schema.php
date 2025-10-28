<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // avoid transactions on managed Postgres
    public $withinTransaction = false;

    public function up(): void
    {
        // Add missing columns expected by the app if they don't exist already
        // and ensure id column has a serial default if possible.
        \Illuminate\Support\Facades\DB::statement(/** @lang sql */ <<<'SQL'
DO $do$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='numero_compte') THEN
        ALTER TABLE comptes ADD COLUMN numero_compte varchar(64);
        CREATE UNIQUE INDEX IF NOT EXISTS comptes_numero_compte_unique ON comptes (numero_compte);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='titulaire_compte') THEN
        ALTER TABLE comptes ADD COLUMN titulaire_compte varchar(255);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='type_compte') THEN
        ALTER TABLE comptes ADD COLUMN type_compte varchar(64);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='devise') THEN
        ALTER TABLE comptes ADD COLUMN devise varchar(8);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='date_creation') THEN
        ALTER TABLE comptes ADD COLUMN date_creation date;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='statut_compte') THEN
        ALTER TABLE comptes ADD COLUMN statut_compte varchar(32) DEFAULT 'actif';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='motif_blocage') THEN
        ALTER TABLE comptes ADD COLUMN motif_blocage text;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='version') THEN
        ALTER TABLE comptes ADD COLUMN version integer DEFAULT 1;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='client_id') THEN
        ALTER TABLE comptes ADD COLUMN client_id varchar(36);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='archived') THEN
        ALTER TABLE comptes ADD COLUMN archived boolean DEFAULT false;
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='comptes' AND column_name='id') THEN
        -- attempt to ensure id has a serial-like default using pg_get_serial_sequence
        PERFORM (
            CASE
                WHEN (select pg_get_serial_sequence('comptes','id')) IS NOT NULL THEN
                    NULL
                ELSE
                    NULL
            END
        );
        -- if there is no serial sequence attached, try to set default using pg_get_serial_sequence result
        BEGIN
            EXECUTE format('ALTER TABLE comptes ALTER COLUMN id SET DEFAULT nextval(pg_get_serial_sequence(''comptes'',''id''))');
        EXCEPTION WHEN OTHERS THEN
            -- ignore - some setups won't allow altering (keep existing id as-is)
            RAISE NOTICE 'could not set serial default for comptes.id: %', SQLERRM;
        END;
    END IF;
END
$do$;
SQL
        );
    }

    public function down(): void
    {
        // Do not drop columns on down to avoid data loss; keep down idempotent and safe.
    }
};
