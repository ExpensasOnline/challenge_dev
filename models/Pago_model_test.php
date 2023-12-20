<?php
class Pago_model_test extends MY_Model {
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

    }

    public function pagarExpensa($datosPago)
    {
			$datosPago['tipo_conciliacion'] = 'automatica';
			$this->db->insert('pago', $datosPago);
    }

		public function pagarExpensaManual($datosPago)
		{
			$datosPago['tipo_conciliacion'] = 'manual';
			$this->db->insert('pago', $datosPago);
		}

}
