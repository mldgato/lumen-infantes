<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cui',
        'first_name',
        'middle_name',
        'surname',
        'second_surname',
        'married_surname',
        'name',
        'civil_status',
        'birthdate',
        'gender',
        'email',
        'password',
        'cellphone',
        'personal_email',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $nameParts = array_filter([
                $model->first_name,
                $model->middle_name,
                $model->surname,
                $model->second_surname,
                $model->married_surname,
            ]);

            $model->name = implode(' ', $nameParts);
        });
    }

    /**
     * Get the user's age based on birthdate.
     */
    public function getAgeAttribute()
    {
        return $this->birthdate ? Carbon::parse($this->birthdate)->age : null;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relaciones
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function professor()
    {
        return $this->hasOne(Professor::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    //relación 1 a 1 polimórfica

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    /**
     * Devuelve la URL de la imagen de perfil para AdminLTE.
     */
    public function adminlte_image()
    {
        // Si el usuario tiene una imagen registrada en la base de datos
        if ($this->image) {
            return asset('storage/' . $this->image->url);
        }

        // Si por alguna razón no tiene imagen, devolvemos un avatar genérico como plan B
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=fff&size=160';
    }

    /**
     * Devuelve la descripción del usuario para AdminLTE (Ej: Su Rol).
     */
    public function adminlte_desc()
    {
        // Buscamos el primer rol del usuario. Si no tiene, mostramos 'Usuario'
        $role = $this->roles()->first();

        return $role ? $role->name : 'Usuario de EduCheck';
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
