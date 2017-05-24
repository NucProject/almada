<?php

namespace App\Models\Base;

/**
 * @package App\Models
 * Class AdDeviceTypeBase
 *
 * Generated by 'php artisan model:create'
 * Properties as follows
 * @property $type_id    
 * @property $type_name    
 * @property $type_desc    
 * @property $status    
 * @property $create_time    
 * @property $update_time    
 */
class AdDeviceTypeBase extends BaseModel
{
    /**
     * Table name
     */
    protected $table = 'ad_device_type';

    /**
     * Table primary-key
     */
    protected $primaryKey = 'type_id';



}

