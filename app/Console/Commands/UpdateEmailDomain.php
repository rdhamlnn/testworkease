<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEmailDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:update-domain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update semua email dari example.com ke kce.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update email dari example.com ke kce.com...');
        
        // Mapping email lama ke email baru
        $emailMappings = [
            'admin@example.com' => 'admin@kce.com',
            'kadivmekanik@example.com' => 'kadivmekanik@kce.com',
            'mekanik@example.com' => 'mekanik@kce.com',
            'logistik@example.com' => 'logistik@kce.com',
            'purchasing@example.com' => 'purchasing@kce.com',
            'atasan@example.com' => 'atasan@kce.com',
            'kadivproduksi@example.com' => 'kadivproduksi@kce.com',
            'kadivplasma@example.com' => 'kadivplasma@kce.com',
            'kadivqc@example.com' => 'kadivqc@kce.com',
        ];
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($emailMappings as $oldEmail => $newEmail) {
            // Cek apakah email lama ada
            $akun = DB::table('akun')->where('email', $oldEmail)->first();
            
            if ($akun) {
                // Cek apakah email baru sudah ada
                $newEmailExists = DB::table('akun')->where('email', $newEmail)->exists();
                
                if ($newEmailExists) {
                    $this->warn("Email {$newEmail} sudah ada, skip update {$oldEmail}");
                    $skipped++;
                } else {
                    // Update email
                    DB::table('akun')
                        ->where('email', $oldEmail)
                        ->update([
                            'email' => $newEmail,
                            'updated_at' => now(),
                        ]);
                    
                    $this->info("✓ Updated: {$oldEmail} → {$newEmail}");
                    $updated++;
                }
            } else {
                // Cek apakah email baru sudah ada
                $newEmailExists = DB::table('akun')->where('email', $newEmail)->exists();
                
                if (!$newEmailExists) {
                    $this->warn("Email {$oldEmail} tidak ditemukan dan {$newEmail} juga tidak ada");
                    $skipped++;
                } else {
                    $this->info("✓ Email {$newEmail} sudah ada, tidak perlu update");
                }
            }
        }
        
        $this->newLine();
        $this->info("Selesai! Updated: {$updated}, Skipped: {$skipped}");
        
        return Command::SUCCESS;
    }
}
