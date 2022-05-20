<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createCustomer(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'phone' => 'required'
            ]);
            $result = DB::insert('INSERT INTO customer( Name, Phone)
                                    VALUES (?,?)',
                [
                    $request->get('name'),
                    $request->get('phone')
                ]);
            if($result){
                return response(['message' => 'Record Inserted Successfully'], 201);
            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createVehicle(Request $request)
    {
        try {
            $request->validate([
                "vehicleID"=> 'required',
                "description"=> 'required',
                "year"=> 'required',
                "type"=> 'required',
                "category"=> 'required'
            ]);
            $result = DB::insert('INSERT INTO vehicle(VehicleID, Description, Year, Type, Category)
                                    VALUES (?,?,?,?,?)',
                [
                    $request->get('vehicleID'),
                    $request->get('description'),
                    $request->get('year'),
                    $request->get('type'),
                    $request->get('category')
                ]);
            if($result){
                return response(['message' => 'Record Inserted Successfully'], 201);
            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRental(Request $request)
    {
        try {

//            print_r($request->all());
//            die(' in here rental');
            $request->validate([
                "CustomerName" => "required",
                "VehicleID"=> "required",
                "StartDate"=> "required",
                "RentalType"=> "required",
                "Qty"=> "required",
//                "ReturnDate"=> "required",
                "TotalAmount"=> "required",
                "Paid"=> "required"
            ]);
            $paymentDate = $request->get('Paid') ? date("Y-m-d"): NULL;
            $returned = '0';
            $custID = $this->getCustomerId($request->get('CustomerName'));
            $days = $request->get('RentalType') * $request->get('Qty');
            $result = DB::insert('INSERT INTO rental VALUES (?,?,?,CURRENT_DATE,?,?,?,?,?,?)',
                [
                    $custID,
                    $request->get('VehicleID'),
                    $request->get('StartDate'),
                    $request->get('RentalType'),
                    $request->get('Qty'),
                    date('Y-m-d', strtotime($request->get('StartDate'). ' + '.$days.' days')),
//                    $request->get('ReturnDate'),
                    $request->get('TotalAmount'),
                    $paymentDate,
                    $returned
                ]);
            if($result){
                return response(['message' => 'Record Inserted Successfully'], 201);
            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getReturnVehicleInfo(Request $request)
    {
        try {
            $request->validate([
                "customerName"=> 'required',
                "vehicleID"=> 'required',
                "returnDate"=> 'required'
            ]);

            $custId = $this->getCustomerId($request->get('customerName'));

            $returned = '0';
            $result = DB::select('Select c.Custid, c.Name,
                v.VehicleID, v.Description, r.ReturnDate, r.TotalAmount
                FROM customer c, vehicle v, rental r
                WHERE r.CustID = c.CustID AND r.VehicleID = v.VehicleID
                AND r.PaymentDate IS NULL AND r.Returned = ?
                AND r.CustID = ? AND r.VehicleID = ?
                AND r.ReturnDate = ?', [
                $returned,
                $custId,
                $request->get('vehicleID'),
                $request->get('returnDate'),

            ]);
            $result = json_decode(json_encode($result), true);
//            print_r($result);
            if($result){
                return response([
                    'message' => 'Record found',
                    'data' => $result
                ]);
            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }

    public function updatePaymentStatus(Request $request){
        try {
//            print_r($request->all());
//            die('in here');
            if($request->get('PaymentStatus')){
                $returned = '1';
                $result = DB::update('Update rental
                        SET PaymentDate = CURRENT_DATE, Returned = ?
                        WHERE CustID = ?
                        AND VehicleID = ?
                        AND ReturnDate = ?',
                    [
                        $returned,
                        $request->get('Custid'),
                        $request->get('VehicleID'),
                        $request->get('ReturnDate'),
                    ]);
                if($result){
                    return response(['message' => 'Record Updated Successfully'], 200);
                }

            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }

    public function getCustomerId($name){
        try {
              $result = DB::select('Select * from customer where Name = ?',
                  [$name]);
            $result = json_decode(json_encode($result), true);
            if($result){
//                return response([
//                    'message' => 'Record found',
//                    'data' => $result[0]['CustID']
//                ]);
                return $result[0]['CustID'];
            }
            return response(['error' => 'Something went wrong']);
        } catch(\Exception $e){
            die($e->getMessage());
        }
    }

    public function getCustomerData(Request $request){
        try {
            $custId = $request->get('custId') ? $request->get('custId') : '';
            $customerName = $request->get('customerName') ? $request->get('customerName') : '';

            if($custId && $customerName){

                $result = DB::select('Select
                CustomerId, CustomerName, CONCAT(? , SUM(RentalBalance) + 0.00) as RemainingBalance
                FROM vrentalinfo
                WHERE CustomerId = ?
                AND CustomerName LIKE ?
                GROUP BY CustomerId', ['$', $custId, '%'.$customerName.'%']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }else if($custId){
                $result = DB::select('Select
                CustomerId, CustomerName, CONCAT(? , SUM(RentalBalance) + 0.00) as RemainingBalance
                FROM vrentalinfo
                WHERE CustomerId = ?
                GROUP BY CustomerId', ['$', $custId]);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            } else if($customerName){
                $result = DB::select('Select
                CustomerId, CustomerName, CONCAT(? , SUM(RentalBalance) + 0.00) as RemainingBalance
                FROM vrentalinfo
                WHERE CustomerName LIKE ?
                GROUP BY CustomerId', ['$', '%'.$customerName.'%']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }else{
                $result = DB::select('Select
                CustomerId, CustomerName, CONCAT(? , SUM(RentalBalance) + 0.00) as RemainingBalance
                FROM vrentalinfo
                GROUP BY CustomerId
                Order BY SUM(RentalBalance)', ['$']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }
            return response(['message' => 'Record not found']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }

    public function getVehicles(Request $request){
        try {
            $vin = $request->get('vin') ? $request->get('vin') : '';
            $description = $request->get('description') ? $request->get('description') : '';

            if($vin && $description){

                $result = DB::select('Select
                                    v.VehicleID as VIN,
                                    v.Description,
                                    CONCAT( ?, AVG(r.TotalAmount) + 0.00) as AverageDailyPrice
                                    FROM vehicle v, rental r
                                    where v.VehicleID = r.VehicleID
                                    AND v.VehicleID = ?
                                    AND v.Description LIKE ?
                                    GROUP BY v.VehicleID, v.Description',
                                    ['$', $vin, '%'.$description.'%']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }else if($vin){
                $result = DB::select('Select
                                    v.VehicleID as VIN,
                                    v.Description,
                                    CONCAT( ?, AVG(r.TotalAmount) + 0.00) as AverageDailyPrice
                                    FROM vehicle v, rental r
                                    where v.VehicleID = r.VehicleID
                                    AND v.VehicleID = ?
                                    GROUP BY v.VehicleID, v.Description',
                    ['$', $vin]);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            } else if($description){
                $result = DB::select('Select
                                    v.VehicleID as VIN,
                                    v.Description,
                                    CONCAT( ?, AVG(r.TotalAmount) + 0.00) as AverageDailyPrice
                                    FROM vehicle v, rental r
                                    where v.VehicleID = r.VehicleID
                                    AND v.Description LIKE ?
                                    GROUP BY v.VehicleID, v.Description',
                    ['$', '%'.$description.'%']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }else{
                $result = DB::select('Select
                                    v.VehicleID as VIN,
                                    v.Description,
                                    CONCAT( ?, AVG(r.TotalAmount) + 0.00) as AverageDailyPrice
                                    FROM vehicle v, rental r
                                    where v.VehicleID = r.VehicleID
                                    GROUP BY v.VehicleID, v.Description
                                    ORDER BY AVG(r.TotalAmount)', ['$']);
                $result = json_decode(json_encode($result), true);
                if($result){
                    return response([
                        'message' => 'Record found',
                        'data' => $result
                    ]);
                }
            }
            return response(['message' => 'Record not found']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }

    public function getAvailableVehicles(Request $request){
        try {
            $returned = '1';
            $result = DB::select('SELECT DISTINCT v.VehicleID, v.Description, v.Year,
                rt.Type, rt.Category, rt.Daily, rt.Weekly FROM rental r
                INNER JOIN vehicle v ON r.VehicleID = v.VehicleID
                INNER JOIN rate rt ON v.Type = rt.Type AND v.Category = rt.Category
                WHERE r.returned = ?

                UNION

                SELECT DISTINCT v.VehicleID, v.Description, v.Year,
                rt.Type, rt.Category, rt.Daily, rt.Weekly FROM vehicle v
                INNER JOIN rate rt ON v.Type = rt.Type AND v.Category = rt.Category
                WHERE v.VehicleID NOT IN (SELECT DISTINCT v.VehicleID from
                vehicle v, rental r where v.VehicleID = r.VehicleID )',
                [$returned]);
            $result = json_decode(json_encode($result), true);
            if($result){
                return response([
                    'message' => 'Records found',
                    'data' => $result
                ]);
            }
            return response(['error' => 'Something went wrong']);
        } catch (\Exception $e){
            die($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
