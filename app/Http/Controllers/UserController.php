<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class UserController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        try {
            $perPage = $request->has('perPage') ? $request->perPage : 50;
            $page = $request->has('page') ? $request->page : 1;

            $users = User::query();
            return $this->success("Usuários listados com sucesso", $users->paginate($perPage, "*", null, $page));
        }catch (\Exception $e){
            return $this->errors("Não foi possível listar os usuários", 400, $e->getMessage());
        }
    }

    public function new(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                "name" => "required|min:3",
                "password" => "required|min:8",
                "email" => "required|unique:users|email"
            ]);

            if ($validator->fails()){
                return $this->errors("Não foi possível cadastrar o usuário", 422, $validator->errors()->all());
            }

            $data = $request->all();

            $user = User::create([
                "name" => $data["name"],
                "password" => bcrypt($data["password"]),
                "email" => $data["email"]
            ]);

            return $this->success("Usuário criado com sucesso", ["user" => $user]);
        }catch (Exception $e){
            return $this->errors("Desculpe, houve um erro ao cadastrar o usuário", 401);
        }
    }

    public function update(int $userId, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                "name" => "required|min:3"
            ]);

            if ($validator->fails()){
                return $this->errors("Não foi possível cadastrar o usuário", 422, $validator->errors()->all());
            }

            $data = $request->all();
            $dataUser = [
                "name" => $data["name"]
            ];

            if (isset($data["password"])){
                $dataUser["password"] = bcrypt($data["password"]);
            }

            $user = User::query()->findOrFail($userId);
            $user->update($dataUser);

            return $this->success("Usuário atualizado com sucesso", ["user" => $user]);
        }catch (\Exception $e){
            return $this->errors("Não foi possível atualizar o usuário", 400, $e->getMessage());
        }
    }

    public function delete(int $userId): JsonResponse
    {
        try {
            $user = User::query()->findOrFail($userId);
            $user->delete();

            return $this->success("Usuário removido com sucesso");
        }catch (\Exception $e){
            return $this->errors("Não foi possível remover o usuário", 400, $e->getMessage());
        }
    }
}
