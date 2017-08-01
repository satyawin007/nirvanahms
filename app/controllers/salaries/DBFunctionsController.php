<?php namespace salaries;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
class DBFunctionsController extends \Controller {

	/**
	 * add a record to a table.
	 *
	 * @return Response
	 */
	public function insert($table, $fields)
	{
		$entity = new $table();
		foreach($fields as $key=>$value){
			$entity[$key] = $value;
		}
		$entity["createdBy"] = \Auth::user()->id;
		return $entity->save();
	}
	
	/**
	 * add a record to a table.
	 *
	 * @return Response
	 */
	public function insertRetId($table, $fields)
	{
		$entity = new $table();
		foreach($fields as $key=>$value){
			$entity[$key] = $value;
		}
		$entity["createdBy"] = \Auth::user()->id;
		$entity->save();
		return $entity->id;
	}
	
	/**
	 * update a record of a table
	 *
	 * @return Response
	 */
	public function update($table, $fields, $data)
	{	
		//print_r($data); print_r($table); print_r($fields); die();
		$fields["updatedBy"] = \Auth::user()->id;
		return $table::where('id', $data['id'])->update($fields);
	}
	
	/**
	 * update a record of a table
	 *
	 * @return Response
	 */
	public function updateEmpDueAmout($table, $fields, $data)
	{
		//print_r($data); print_r($table); print_r($fields); die();
		$fields["updatedBy"] = \Auth::user()->id;
		return $table::where('id', $data['id'])->update($fields);
	}
	
	/**
	 * update a record of a table
	 *
	 * @return Response
	 */
	public function updateSalaryTransaction($table, $fields, $data)
	{
		//print_r($data); print_r($table); print_r($fields); die();
		$fields["updatedBy"] = \Auth::user()->id;
		return $table::where('empId', $data['eid'])->where("salaryMonth","=",$data['month'])->update($fields);
	}
	public function updateSalaryDetails($table1, $fields1, $data1)
	{
		//print_r($data1); print_r($table1); print_r($fields1); die();
		$fields1["updatedBy"] = \Auth::user()->id;
		return $table1::where('empId', $data1['empId'])->update($fields1);
	}
	
	/**
	* get a record from a table
	*
	* @return Response
	*/
	public function get($table, $fields)
	{
		return $table::where($fields)->get();
	}
	
	
}
