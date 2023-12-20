<?php
class Pago_test extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pago_model');
	}
//esta funcion recibe los datos de la API de pagos para realizar automaticamente el pago de una expensa
	public function pagarExpensa()
	{
		$this->load->model('expensa_model');
		$this->load->model('ingresos_model');
		$datosPagoExpensa = json_decode(trim(file_get_contents('php://input')), true);

		//cambiamos el formato de fecha
		list($dia,$mes,$anio) = explode('/', $datosPagoExpensa['fechaPago'], 3);
		$datosPagoExpensa['fechaPago']= $anio.'-'.$mes.'-'.$dia;

		$expensa = $this->expensa_model->getById($datosPagoExpensa["idExpensa"]);
		$fechaPago=strtotime($datosPagoExpensa['fechaPago']);
		$primerVencimiento=strtotime($expensa->primer_vencimiento);
		$segundoVencimiento=strtotime($expensa->segundo_vencimiento);
		//calculamos los intereses que debe pagar
		$montoOriginal = $expensa->monto;
		if ($fechaPago <= $primerVencimiento) {
			$montoTotalExpensa = $montoOriginal;
		} elseif ($fechaPago <= $segundoVencimiento) {
			$montoTotalExpensa = $montoOriginal + ($montoOriginal * $expensa->porcentaje_interes/100);
		} else{
			$mesesDiferencia=(((int)date('Y', $datosPagoExpensa['fechaPago']) - (int)date('Y', $expensa->segundo_vencimiento)) * 12) + ( (int)date('n', $datosPagoExpensa['fechaPago']) - (int)date('n', $expensa->segundo_vencimiento));
			$montoTotalExpensa = round($montoOriginal + ($expensa->porcentaje_recargo_mensual/100 * $mesesDiferencia),2);
		}
		if($datosPagoExpensa['monto'] == $montoTotalExpensa){//si el monto de pago coincide con el monto calculado de la expensa
			$pago=array();
			$pago['fechaPago']=$datosPagoExpensa['fechaPago'];
			$pago['metodo']=$datosPagoExpensa['metodo'];
			$pago['idExpensa']=$datosPagoExpensa['idExpensa'];
			$pago['monto']=$datosPagoExpensa['monto'];
			$this->pago_model_test->pagarExpensa($pago);
			$cuentaContable = $this->ingresos_model->obtenerCuentaContable($datosPagoExpensa['idExpensa']);
			$edificio = $this->ingresos_model->obtenerEdificio($datosPagoExpensa['idExpensa']);
			$this->ingresos_model->registrarIngresoCaja($datosPagoExpensa['monto'],$datosPagoExpensa['fechaPago'],$edificio->id,$cuentaContable->id);
		}else{
			//generar error de pago
		}

	}

}
