<?php
namespace Src\Modules\Recurso\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecursoRequest;
use Src\Modules\Recurso\Application\Dtos\StoreRecursoDto;
use Src\Modules\Recurso\Application\Services\StoreRecursoService;
use InvalidArgumentException;
use Src\Modules\Recurso\Domain\Exceptions\CombustibleNotFoundInEstacion;

class StoreRecursoController extends Controller
{
    public function __construct(
        private StoreRecursoService $storeRecursoService
    ) {}

    public function __invoke(StoreRecursoRequest $request)
    {
        try {
            $storeRecursoDto = new StoreRecursoDto(
                litros: $request->litros,
                litros_disponibles: $request->litros,
                monto: $request->monto,
                tipo_combustible_id: $request->tipo_combustible_id,
                centro_costo_id: $request->centro_costo_id,
                numero_factura: $request->numero_factura,
                orden: 0,
                emitido_por: auth()->user()->id
            );


            $this->storeRecursoService->execute($storeRecursoDto);


            return redirect()->route('recursos.index')->with('success', 'Recurso creado exitosamente.');

        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }catch (CombustibleNotFoundInEstacion $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()])->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
