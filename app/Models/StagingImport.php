<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingImport extends Model
{
    protected $table = 'staging_import';

    protected $fillable = [
        'nama_file',
        'periode_id',
        'jenis_bansos_id',
        'raw_data',
        'status',
        'error_log',
        'imported_by',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_data'    => 'array',  // JSON otomatis encode/decode
            'error_log'   => 'array',  // JSON otomatis encode/decode
            'imported_at' => 'datetime',
            'periode_id'      => 'integer',
            'jenis_bansos_id' => 'integer',
            'imported_by'     => 'integer',
        ];
    }

    // =========================================================
    // Konstanta status
    // =========================================================

    const STATUS_PENDING  = 'pending';
    const STATUS_VALID    = 'valid';
    const STATUS_INVALID  = 'invalid';
    const STATUS_IMPORTED = 'imported';

    // =========================================================
    // Scopes
    // =========================================================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeValid($query)
    {
        return $query->where('status', self::STATUS_VALID);
    }

    public function scopeInvalid($query)
    {
        return $query->where('status', self::STATUS_INVALID);
    }

    public function scopeByFile($query, string $namaFile)
    {
        return $query->where('nama_file', $namaFile);
    }

    // =========================================================
    // Helpers
    // =========================================================

    public function tandaiValid(): void
    {
        $this->update(['status' => self::STATUS_VALID, 'error_log' => null]);
    }

    public function tandaiInvalid(array $errors): void
    {
        $this->update(['status' => self::STATUS_INVALID, 'error_log' => $errors]);
    }

    public function tandaiImported(int $userId): void
    {
        $this->update([
            'status'      => self::STATUS_IMPORTED,
            'imported_by' => $userId,
            'imported_at' => now(),
        ]);
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function jenisBansos()
    {
        return $this->belongsTo(JenisBansos::class, 'jenis_bansos_id');
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}