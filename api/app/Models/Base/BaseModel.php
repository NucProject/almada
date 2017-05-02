<?php
namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class BaseModel
 * @package App\Models
 *
 */
class BaseModel extends Model
{
    protected $mustHave = [];

    protected $table;

    protected $dateFormat = 'U';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    const RETURN_TYPE_ARR = 'arr';

    const RETURN_TYPE_OBJECT = 'object';

    /**
     * 添加 ValidScope:
     * 查询所有有效的数据(status!=0)
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ValidScope());
    }

    /**
     * @param $selects
     * @return \Illuminate\Database\Eloquent\Builder
     * 指代查询所有的, 那么就会去掉对status!=0的条件.
     */
    public static function queryAll(array $selects=[])
    {
        $query = self::query()->withoutGlobalScope(new ValidScope);
        if ($selects)
        {
            $selectFields = [];
            foreach ($selects as $tableName => $fieldNames)
            {
                foreach ($fieldNames as $fieldName)
                {
                    $selectFields[] = "{$tableName}.{$fieldName}";
                }
            }

            return $query->select($selectFields);
        }
        return $query;
    }

    /**
     * 检查必填字段
     * @param array $request
     * @return bool
     */
    public function badSaveRequest(array $request)
    {
        $columnList = $this->getColumnList();
        foreach ($columnList as $column) {
            if ($column == $this->primaryKey) {
                continue;
            }
            
            if (isset($this->mustHave[$column])) {
                $value = isset($request[$column]) ? $request[$column] : '';
                if (is_array($this->mustHave[$column])) {
                    if (! in_array($value, $this->mustHave[$column])) {
                        return true;
                    }
                } elseif (empty($value) || ! $this->mustHave[$column]($value)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 检查保存参数
     * @param array $request
     * @return bool
     */
    public function badUpdateRequest(array $request)
    {
        foreach ($request as $column => $value) {
            if (isset($this->mustHave[$column])) {
                if (is_array($this->mustHave[$column])) {
                    if (! in_array($request[$column], $this->mustHave[$column])) {
                        return true;
                    }
                } elseif (empty($request[$column]) || ! $this->mustHave[$column]($request[$column])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取表的字段
     * @return mixed
     */
    public function getColumnList()
    {
        static $columnList;
        if ($columnList) {
            return $columnList;
        }

        $connection = DB::connection();
        $columnList = $connection->getSchemaBuilder()->getColumnListing($this->table);
        return $columnList;
    }

    /**
     * 保存表字段，插入或更新
     * @param array $request
     * @return bool
     */
    public function save(array $request = [])
    {
        if ($request) {
            $columnList = $this->getColumnList();
            foreach ($columnList as $column) {
                if (isset($request[$column])) {
                    $this->$column = $request[$column];
                }
            }
        }
        
        return parent::save();
    }

    /**
     * 更新表字段
     * @param array $request   
     * @return bool
     */
    public function update2(array $request = [])
    {
        return $this->save($request);
    }

    /**
     * 读取不可访问属性的值时
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $key = Str::snake($key);
        return parent::__get($key);
    }

    /**
     * @在给不可访问属性赋值时
     * @param string $key            
     * @param mixed $value            
     */
    public function __set($key, $value)
    {
        $key = Str::snake($key);
        return parent::__set($key, $value);
    }

    /**
     * @param array $data
     * @param bool|true $safe
     * @param bool|array $fields
     * 如果safe=true则检查Model的$fillable成员, 否则不检查. 注意!
     * @return void
     */
    public function setAttributes(array $data, $safe=true, $fields=false)
    {
        $fieldsData = self::convertToSnakeCaseKeyAndValues($data, $fields);
        if ($safe)
        {
            $this->fill($fieldsData);
        }
        else
        {
            // 不安全实现(为了略过$fillable成员)
            foreach ($fieldsData as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    /**
     * @param array $data
     * @param $fields
     * @return array
     */
    public static function convertToSnakeCaseKeyAndValues(array $data, $fields)
    {
        $result = [];
        foreach ($data as $key => $value)
        {
            // 过滤fields
            if ($fields) {
                if (!in_array($key, $fields)) {
                    continue;
                }
            }
            $key = Str::snake($key);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 分页获得数据表内容
     * @param Model  $builder
     * @param array  $where 筛选条件 ['字段名', '表达式', '值']
     * @param Mixed  $callback
     * @param int    $page          页数
     * @param int    $limit  分页大小
     * @param array  $field 字段名
     * @param string $returnType  返回类型： arr, object
     *            
     * @return int|null
     */
    public function pager(Model $builder, $where = [], $callback = "", $page = 1, 
        $limit = 10, $field = ['*'], $returnType = 'arr')
    {
        $info = [];
        $info['page'] = $page;
        $info['limit'] = $limit;
        //$builder = $model;
        
        if (! empty($where)) {
            foreach ($where as $row) {
                $builder = $builder->where($row[0], $row[1], $row[2]);
            }
        }
        
        if (isset($builder->getQuery()->groups)) {
            $cnt = count($builder->get($field));
        } else {
            $cnt = $builder->count();
        }
        
        $info['total'] = $cnt;
        
        if (!empty($callback)) {
            $builder = call_user_func($callback, $builder);
        }
        
        $offset = (max(1, $page) - 1) * (int) $limit;
        $info['list'] = $builder->offset($offset)
            ->take($limit)
            ->get($field);
        if ($returnType == 'arr') {
            $info['list'] = $info['list']->toArray();
        }
        
        return $info;
    }

    public function toCamelArray()
    {
        $returnArray = $array = $this->toArray();
        if (!empty($array)) {
            $returnArray = [];
            foreach ($array as $key => $value) {
                $returnArray[Str::camel($key)] = $value;
            }
        }
        return $returnArray;
    }

    public function getCreateTimeAttribute($value){
        return intval($value);
    }
    
    public function getUpdateTimeAttribute($value){
        return intval($value);
    }

    /**
     * @param $collection   \Illuminate\Database\Eloquent\Collection
     * @param $toArrayMethod string
     * @return array
     */
    public static function toItemsArray($collection, $toArrayMethod='toArray')
    {
        return array_map(function ($value) use($toArrayMethod) {
            return $value instanceof Arrayable
                ? $value->$toArrayMethod()
                : $value->$toArrayMethod();
        }, $collection->all());
    }
}
