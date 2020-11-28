<?php
namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ServerRequestInterface;

class JGridRequestQuery
{
    protected $data;
    protected $query;
    
    
    public function __construct(Builder $query, ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $data = [
            'page' => $params['page'],
            'limit' => $params['rows'],
            'filters' => $params['filters'],
            'sort_column' => $params['sidx'],
            'sort_order' => $params['sord'],
        ];
        $data['limit'] = empty($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 30 ? 10 : $data['limit'];
        $data['page'] = empty($data['page']) || $data['page'] <= 0 ? 1 : $data['page'];
        
        $this->data = $data;
        $this->query = $query;
    }
    
    
    public function withFilters()
    {
        $filters = empty($this->data['filters']) ? null : json_decode($this->data['filters']);
        $conditions = ['bw' => 'like', 'eq' => '='];

        if ($filters instanceof \stdClass && is_array($filters->rules)) {
            foreach ($filters->rules as $rule) {
                if (empty($conditions[$rule->op])) {
                    continue; // unknown condition, skip
                }
                if (!empty($filters->groupOp) && $filters->groupOp == 'AND') {
                    $this->query->where($rule->field, $conditions[$rule->op], $rule->data);
                } else {
                    $this->query->orWhere($rule->field, $conditions[$rule->op], $rule->data);
                }
            }
        }

        return $this;
    }
    
    
    public function withSorting($defaultColumn, $defaultOrder = 'asc')
    {
        $column = !empty($this->data['sort_column']) ? $this->data['sort_column'] : $defaultColumn;
        $order = !empty($this->data['sort_column']) && !empty($this->data['sort_order']) ? $this->data['sort_order'] : $defaultOrder;
        
        $this->query->orderBy($column, $order);
        
        return $this;
    }
    
    
    public function paginate(array $columns = ['*'])
    {
        $total = $this->query->count();
        $this->query
            ->offset(($this->data['page']-1)*$this->data['limit'])
            ->limit($this->data['limit']);
        $rows = $this->query->get()->transform(function(Model $item) use ($columns) {
            $pk = $item->getKeyName();
            return ['id' => $item->$pk, 'cell' => array_intersect_key($item->toArray(), array_flip($columns))]; 
        })->all();

        $response = new \stdClass();
        $response->page = $this->data['page'];
        $response->total =  count($rows);
        $response->records = $total;
        $response->rows = $rows;
        
        return $response;
    }

}