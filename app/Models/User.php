<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'theme',
        // Profile fields
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'license_number',   // pharmacist
        'pharmacy_name',    // pharmacist
        'business_name',    // supplier
        'gst_number',       // supplier
        'website',          // supplier
        'avatar_url',       // profile photo
        'default_profit_pct', // pharmacist global default profit %
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'default_profit_pct'  => 'decimal:2',
        ];
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'pharmacist', 'supplier'], true);
    }

    /**
     * Calculate profile completion percentage based on filled fields.
     * Returns a value 0–100.
     */
    public function profileCompletion(): int
    {
        $fields = ['name', 'email', 'phone', 'address', 'city'];

        if ($this->isCustomer()) {
            $fields[] = 'phone';
        } elseif ($this->isPharmacist()) {
            $fields[] = 'license_number';
            $fields[] = 'pharmacy_name';
        } elseif ($this->isSupplier()) {
            $fields[] = 'business_name';
            $fields[] = 'gst_number';
        }

        $filled = collect($fields)->filter(fn($f) => !empty($this->$f))->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}
