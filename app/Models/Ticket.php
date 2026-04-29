<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Ticket extends Model
{
    use CentralConnection;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'assigned_to',
        'department',
        'priority',
        'subject',
        'category_id',
        'is_archived',
        'is_locked',
        'status',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_locked' => 'boolean',
    ];

    /**
     * Get Tenant RelationShip
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    /**
     * Get Assigned User RelationShip
     */
    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get Messages RelationShip
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'ticket_id')->connection;
    }

    /**
     * Get Categories RelationShip
     */
    public function categories()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * Get Labels RelationShip
     */
    public function labels()
    {
        $this->hasManyThrough(
            TicketLabel::class,
            TicketHasLabel::class,
            'ticket_id',
            'id',
            'id',
            'ticket_label_id'
        );
    }

    public function is_closed()
    {
        return $this->status == 'closed';
    }

    public function is_archived()
    {
        return $this->is_archived;
    }

    public function is_locked()
    {
        return $this->is_locked;
    }

    public static function get_tenant_user($tenant_db, $user_id)
    {
        $template = config('database.default');
        $template_connection = config("database.connections.{$template}");
        $template_connection['database'] = $template_connection['driver'] === 'sqlite' ? database_path($tenant_db) : $tenant_db;

        config()->set('database.connections.dynamic_tenant', $template_connection);

        return DB::connection('dynamic_tenant')->table('users')->where('id', $user_id)->first();
    }
}
