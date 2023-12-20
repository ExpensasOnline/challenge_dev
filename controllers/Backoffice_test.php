<?php
class Backoffice_test extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pago_model');
	}
//hacemos esta funcion para que alguien del equipo de soporte técnico pueda impactar manualmente un pago que falló y no entró automáticamente en sistema
	public function registrarPagoManual()
	{
		$this->load->model('ingresos_model');
		$datosPagoExpensa = json_decode(trim(file_get_contents('php://input')), true);
		$pago=array();
		$pago['fechaPago']=$datosPagoExpensa['fechaPago'];
		$pago['metodo']=$datosPagoExpensa['metodo'];
		$pago['idExpensa']=$datosPagoExpensa['idExpensa'];
		$pago['monto']=$datosPagoExpensa['monto'];

		$this->pago_model->pagarExpensaManual($pago);
		$cuentaContable = $this->ingresos_model->obtenerCuentaContable($datosPagoExpensa['idExpensa']);
		$edificio = $this->ingresos_model->obtenerEdificio($datosPagoExpensa['idExpensa']);
		$this->ingresos_model->registrarIngresoCaja($datosPagoExpensa['monto'],$datosPagoExpensa['fechaPago'],$edificio->id,$cuentaContable->id);
	}
}
