<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AiChatConfig",
 *     title="AI Chat Configuration",
 *     description="Model representing AI chat configuration settings"
 * )
 */
class AiChatConfig extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the configuration setting"
     * )
     * @OA\Property(
     *     property="value",
     *     type="string",
     *     description="The value of the configuration setting"
     * )
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="A description of the configuration setting"
     * )
     */
    protected $fillable = [
        'name', 'value', 'description'
    ];
}