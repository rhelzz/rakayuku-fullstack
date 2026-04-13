<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Material;

use App\Actions\Material\ProcessMaterialFlowAction;
use App\Http\Controllers\Api\Concerns\RespondsWithApiJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Material\ProcessMaterialFlowRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class MaterialFlowController extends Controller
{
    use RespondsWithApiJson;

    public function store(
        ProcessMaterialFlowRequest $request,
        ProcessMaterialFlowAction $processMaterialFlowAction,
    ): JsonResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->error(
                request: $request,
                message: 'Unauthorized.',
                status: 401,
            );
        }

        try {
            $result = $processMaterialFlowAction->handle(
                actor: $user,
                payload: $request->validated(),
            );

            return $this->success(
                request: $request,
                message: 'Material flow processed successfully.',
                data: $result,
            );
        } catch (ValidationException $exception) {
            return $this->error(
                request: $request,
                message: 'Material flow validation failed.',
                errors: $exception->errors(),
                status: 422,
            );
        } catch (ModelNotFoundException) {
            return $this->error(
                request: $request,
                message: 'Data project atau material tidak ditemukan.',
                status: 404,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->error(
                request: $request,
                message: 'Terjadi kesalahan saat memproses alur bahan.',
                status: 500,
            );
        }
    }
}
