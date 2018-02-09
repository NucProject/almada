<?php

namespace App\Models\Base;

/**
 * @package App\Models
 * Class AdGroupBase
 *
 * Generated by 'php artisan model:create'
 * Properties as follows
 * @property $group_id    
 * @property $group_name    
 * @property $group_desc    
 * @property $group_invite    
 * @property $status    
 * @property $create_time    
 * @property $update_time    
 */
class AdGroupBase extends BaseModel
{
    /**
     * Table name
     */
    protected $table = 'ad_group';

    /**
     * Table primary-key
     */
    protected $primaryKey = 'group_id';



}
