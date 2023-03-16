<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPUnit\Exception;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 *
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                "name" => "required|min:3",
                "password" => "required|min:8",
                "email" => "required|unique:users|email"
            ]);

            if ($validator->fails()){
                return $this->errors("Não foi possível cadastrar o usuário", 400, $validator->errors()->all());
            }

            $data = $request->all();

            $user = User::create([
                "name" => $data["name"],
                "password" => bcrypt($data["password"]),
                "email" => $data["email"]
            ]);

            return $this->success("Usuário criado com sucesso", ["user" => $user]);
        }catch (Exception $e){
            return $this->errors("Desculpe, houve um erro ao registrar o usuário", 401);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                "email" => "required",
                "password" => "required",
            ]);

            if ($validator->fails()){
                return $this->errors("Não foi possível fazer login", 422, $validator->errors()->all());
            }

            $data = $request->all();

            if (Auth::attempt($data)){
                $user = Auth::user();

                return $this->success("Usuário autenticado com sucesso", [
                    "token" => $user->createToken("tokenAcesso")->accessToken
                ]);
            }

            return $this->errors(
                "Usuário e/ou senha inválidos",
                401
            );
        }catch (\Exception $e){
            return $this->errors(
                "Não foi possível fazer login",
                400,
                $e->getMessage()
            );
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()){
                return $this->errors("Insira um email válido", 422, $validator->errors()->all(),);
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            $reset = ($status === Password::RESET_LINK_SENT);

            return $this->success("Email de recuperação de senha enviado com sucesso", ['status' => $status, 'reset' => $reset ]);
        }catch (\Exception | TransportException $e){
            return $this->errors("Erro ao enviar email de recuperação de senha", 400, $e->getMessage());
        }
    }

    public function resetPasswordByToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return $this->errors("Desculpe, não foi possível resetar senha", 422, $validator->errors()->all());
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );
            $reset = ($status === Password::PASSWORD_RESET);

            return $this->success("Senha recuperada com sucesso", ['status' => $status, 'reset' => $reset]);
        } catch (Exception $e) {
            return $this->errors("Erro ao recuperar sua senha", 400, $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = Auth::guard('api')->user()->token();
            $token->revoke();
            return $this->success("Usuário deslogado com sucesso");
        }catch (\Exception $e){
            return $this->errors("Não foi possível fazer logout", 400, $e->getMessage());
        }
    }
}
