<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Components\Message;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    use Message;
    /**
     * Method for Login
     */
    public function login(Request $request){
        try {
            
            $validator = Validator::make($request->all(),[
                "email" => ["required","exists:users,email"],
                "password"  => ["required"]
            ]);
                
                
            if($validator->fails()){
                return $this->apiOutput(Response::HTTP_OK, $this->getValidationError($validator));
            }
        

            // only account_type `driver` can login into APP/API
            $user = User::where('email', $request->email)
                        ->first();
            if( empty($user) ){
                return $this->apiOutput(Response::HTTP_FORBIDDEN, "Account doesn't exists, Invalid Mobile Number !");
            }

            if( !Hash::check($request->password, $user->password) ) {
                return $this->apiOutput(Response::HTTP_UNAUTHORIZED, "Invalid Password");
            }


                

                $this->access_token = $user->createToken( $request->device_name ?? ($request->ip() ?? "Unknown") )->plainTextToken;
                $this->data = [
                    'profile' => $user
                ];
                $this->apiSuccess();
                return $this->apiOutput(200, "Login Successfully");
            

            
        } catch (Exception $e) {
            if(isset($e->validator) and count($e->validator->errors())){
                return $this->apiOutput(Response::HTTP_BAD_REQUEST, $e->validator->errors());    
            }else{
                return $this->apiOutput($e->getCode() == Response::HTTP_OK ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR, $this->getExceptionError($e));
            }
        }
    
      
        // return response()->json([
        //     $request->all()
        // ]);
    }
}
