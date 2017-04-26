<?php

namespace App\Models\Base;

/**
 * @package App\Models
 * Class AdDeviceBase
 *
 * Generated by 'php artisan model:create'
 * Properties as follows
 * @property $device_id    
 * @property $device_name    
 * @property $device_desc    
 * @property $group_id    
 * @property $status    
 * @property $create_time    
 * @property $update_time    
 */
class AdDeviceBase extends BaseModel
{
    /**
     * Table name
     */
    protected $table = 'ad_device';

    /**
     * Table primary-key
     */
    protected $primaryKey = 'device_id';



}

