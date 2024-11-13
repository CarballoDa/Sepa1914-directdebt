<?php

class Sepa1914 {
    public int $total_bills = 0;

    private string $country_code = 'ES';
    private string $book_version = '19143'; // SEPA Debtis Book Version

    private string $conductor_identification = '48000'; // Company SEPA BANK identification
    private string $nif = 'EXXXXXXXX'; // Company CIF/NIF
    private string $conductor_name = 'COMPANY S.L.'; // Company Name
    private string $conductor_address_d1 = 'COMPANY ADDRESS'; // Address
    private string $conductor_address_d2 = 'CITY-ZIP_CODE '; // City-Postal Code
    private string $conductor_address_d3 = 'PROVINCE'; // City

    private $debit_date = null;
    private $debit_date_label = null;
    private $debit_hour_label = null;

    private string $account_number = 'ES8352288110411103371673';
    private string $receiving_entity = '3598';
    private string $receiving_office = '3265';
    private string $mandate_signature_date = '20091031';

    private int $total_debits = 0;
    private int $total_debits_amount = 0;
    private string $bills_to_debit_ids = '';

    private string $filesDirectory = '/downloads/debits/';

    private string $resume_file = '';
    private string $send_file = '';

    private int $debit_order_id = 0; // Identificador unico por si se desea guardar reguistro en base de dato y/o nomeclatura de archivos txt

    private string $formatDate = 'Y-m-d';
    private string $formatHour = 'H:d:s';

    private object $conductor = null;
    private array $customers = [];

    /**
     *
     * Constructor
     *
     * Setting actual date and hour values for a new mandate file
     * @return none
     */

    public function __construct()
    {
        $this->debit_date = date($this->formatDate);
        $this->debit_date_label = str_replace('-', '', $this->debit_date);
        $this->debit_hour = date($this->formatHour);
        $this->debit_hour_label = str_replace(':', '', $this->debit_hour);
    }

    /**
     *
     * setConductor
     *
     * Setting conductor personal information (company, freelance, ...)
     * @data array with data
     * @return none
     */

    public function setConductor(array $data): void
    {
        $this->conductor_identification = $data['identification'];
        $this->nif = $data['nif'];
        $this->conductor_name = $data['name'];
        $this->conductor_address_d1 = $data['address'];
        $this->conductor_address_d2 = $data['city'].'-'.$data['postalCode'];
        $this->conductor_address_d3 = $data['city'];
    }

    /**
     *
     * setMandate
     *
     * Setting all bank data to receive payments from customers
     * @data array with data
     * @return none
     */

    public function setMandate(array $data): void
    {
        $this->account_number = $data['accountNumber'];
        $this->receiving_entity = $data['receivingEntity'];
        $this->receiving_office = $data['receivingOffice'];
        $this->mandate_signature_date = $data['mandateSignatureDate'];
    }

    /**
     *
     * setDateFormat
     *
     * Setting date format Ej: 'Y-m-d' for spanish format
     * @format string
     * @return none
     */

    public function setDateFormat(string $format): void
    {
        $this->formatDate = $format;
    }

    /**
     *
     * setHourFormat
     *
     * Setting hour format Ej: 'H:d:s' for spanish format
     * @format string
     * @return none
     */

    public function setHourFormat(string $format): void
    {
        $this->formatHour = $format;
    }

    /**
     *
     * setDebitOrderId
     *
     * Setting Debit Order unique identificator
     * @id int
     * @return none
     */

    public function setDebitOrderId(int $id): void
    {
        $this->debit_order_id = $id;
    }

    /**
     *
     * setFilesDirectory
     *
     * Setting server directory for saving new files
     * @dir string
     * @return none
     */

    public function setFilesDirectory(string $dir): void
    {
        $this->filesDirectory = $dir;
    }





    /*******************************************************************************************************/

    /* CREACIÓN DE DOCUMENTO Y CAMBIOS DE ESTADO */
    protected function createDocument($post){


        $this->send_file = $this->getDocument();
        $this->resume_file = $this->getResumeDocument();

        if($errores > 0){
            $msg['type'] = 'danger';
            $msg['title'] = 'DB Error';
            $msg['text'] = 'Descripcion del error';
        }else{
            $msg['type'] = 'success';
            $msg['title'] = 'Operación Realizada';
            $msg['text'] = 'se han creado los ficheros';
            $msg['newuid'] = $post['uid'];
            $msg['file_1'] = $this->send_file;
            $msg['file_2'] = $this->resume_file;
        }

        return $msg;
    }

    public function getResumeDocument(): string
    {
        $fileName = 'C14_'.$this->addZerosBefore($this->debit_order_id, 8).'_resume.txt';
        $docDir = $_SERVER['DOCUMENT_ROOT'].$this->filesDirectory.$fileName;
        $old = umask(0);
        $myfile = fopen($docDir, "w") or die("Unable to open file : $docDir");
        umask($old);
        // Resume File Content
        fwrite($myfile, 'File Name: '.str_replace('_resume', '', $fileName).'.txt' );
        fwrite($myfile,	"\r\n"); // Comprobar si puede sustituirse esta mlinea por un PHP_EOL al final de cada linea con texto
        fwrite($myfile, 'Break Lines: YES' );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Format: CSB 19-14' );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Conductor: '.$this->conductor_name);
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'NIF: '.$this->nif );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Orderer: '.$this->conductor_name);
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'NIF: '.$this->nif );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Number of charges: '.$this->total_debits );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Total amount: '.number_format($this->total_debits_amount, 2, '.', ',').' Euros' );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Created on date: '.date("d-m-Y",strtotime($this->debit_date)) );
        fwrite($myfile,	"\r\n");
        fwrite($myfile, 'Charge on date: '.date("d-m-Y",strtotime($this->debit_date)) );
        // Guardar el archivo
        fclose($myfile);
        return $doc;
    }





    public function getDocument(){
        $fileName = 'C14_'.$this->addZerosBefore($this->debit_order_id, 8).'.txt';
        $docDir = $_SERVER['DOCUMENT_ROOT'].$this->filesDirectory.$fileName;
        $old = umask(0);
        $myfile = fopen($docDir, "w") or die("Unable to open file : $docDir");
        umask($old);
        // Header: Conductor Info
        fwrite($myfile, $this->getFirstRowDoc());
        fwrite($myfile,	"\r\n");
        fwrite($myfile, $this->getSecondRowDoc());
        fwrite($myfile,	"\r\n");
        // Body: Direct Debits
        $clientes_facturas = $this->getActiveDebitsData();
        foreach($clientes_facturas as $cliente_factura){
            fwrite($myfile, $this->getDebitRowDoc($cliente_factura));
            fwrite($myfile,	"\r\n");
        }
        // Footer: Resume Direct Debits info
        fwrite($myfile, $this->getAntepenultimateRowDoc());
        fwrite($myfile,	"\r\n");
        fwrite($myfile, $this->getPenultimateRowDoc());
        fwrite($myfile,	"\r\n");
        fwrite($myfile, $this->getFinalRowDoc());
        fwrite($myfile,	"\r\n");
        // Saving File
        fclose($myfile);
        return $doc;
    }

    /**
     *
     * replaceBadCharacters
     *
     * Replacing non acepted characters
     * @str string
     * @return string
     */

    private function replaceBadCharacters(string $str): string
    {
        $str = str_replace('Ñ', 'N', $str);
        $str = str_replace('ñ', 'n', $str);
        $str = str_replace('Ç', 'C', $str);
        $str = str_replace('ç', 'c', $str);
        return $str;
    }

    /**
     *
     * adjustLength
     *
     * Adjusting data string length to max line length required
     * @str string
     * @max_length int default 600
     * @fillWith string for fill empty spaces until max length required. Default blank space ' '
     * @return string
     */

    private function adjustLength(string $str, int $max_length = 600, string $fillWith = ' '): string
    {
        $str_spaces = '';
        $data_length = strlen($str);
        if($data_length < $max_length){
            $str_spaces = $this->addEmptySpace($max_length - $data_length, $fillWith );
        }elseif($data_length > $max_length){
            die('more than '.$max_length.' characters in this line/field : '.$str);
        }
        return $str.$str_spaces;
    }

    /**
     *
     * addEmptySpace
     *
     * Create an string with the corret length for adding to some string value
     * @spaces_num int
     * @fillWith string for fill empty spaces until max length required. Default blank space ' '
     * @return string
     */

    private function addEmptySpace(int $spaces_num, string $fillWith = ' '): string
    {
        $str = '';
        for($i=1;$i<=$spaces_num;$i++){
            $str .= $fillWith;
        }
        return $str;
    }

    /**
     *
     * addZerosBefore
     *
     * Create an string with n quentity of zeros after the provided string in $str
     * @str string
     * @limit int
     * @return string
     */

    private function addZerosBefore(string $str, int $limit): string
    {
        $strZeros = '';
        for($i=1;$i<=$limit - strlen($str);$i++){
            $strZeros .= '0';
        }
        return $strZeros.$str;
    }


    /*******************************************************************************************************/

    // Registro de Cabecera del presentador ( N1914 pag 17 )
    private function getFirstRowDoc(){
        $str = '';
        $codigo_registro = '01';
        $numero_dato = '001';

        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->book_version, 5); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($numero_dato, 3); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->country_code.$this->conductor_identification.$this->nif, 35); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->conductor_name, 70); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->debit_date_label, 8); // Ajustar longitud de dato a longitud máxima posible
        $microtime = '00000';
        $identificacion_fichero = 'PRE'.$this->debit_date_label.$this->debit_hour_label.$microtime;
        $referencia_fichero = $this->addZerosBefore($this->debit_order_id, 13);
        $str .= $this->adjustLength($identificacion_fichero.$referencia_fichero, 35); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->receiving_entity, 4); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->receiving_office, 4); // Ajustar longitud de dato a longitud máxima posible

        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres.
        $str = $this->replaceBadCharacters($str);

        return $str;
    }
    // Registro de Cabecera de acreedor n fecha n ( N1914 pag 19 )
    private function getSecondRowDoc(){
        $str = '';
        $codigo_registro = '02';
        $numero_dato = '002';

        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->book_version, 5); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($numero_dato, 3); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->country_code.$this->conductor_identification.$this->nif, 35); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->debit_date_label, 8); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->conductor_name, 70); // Ajustar cantidad de espacios que puede ocupar el dato
        $str .= $this->adjustLength($this->conductor_address_d1, 50); // Ajustar cantidad de espacios que puede ocupar el dato
        $str .= $this->adjustLength($this->conductor_address_d2, 50); // Ajustar cantidad de espacios que puede ocupar el dato
        $str .= $this->adjustLength($this->conductor_address_d3, 40); // Ajustar cantidad de espacios que puede ocupar el dato
        $str .= $this->adjustLength($this->country_code, 2); // Ajustar longitud de dato a longitud máxima posible
        $str .= $this->adjustLength($this->account_number, 34); // Ajustar longitud de dato a longitud máxima posible

        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres
        $str = $this->replaceBadCharacters($str);

        return $str;
    }
    // Registro Individual de adeudo ( N1914 pag 21 )
    private function getDebitRowDoc($data){
        $str = '';
        $codigo_registro = '03';
        $numero_dato = '003';

        $cliente = $data['cliente'];
        $facturas = $data['facturas'];
        $log_factura = ['log'];

        $facturas_a_domiciliar_label = '';
        $importe_total = 0;
        foreach($facturas as $factura){
            //$importe_total += $factura['importe_total'];
            $imp_factura = $factura['importe_total'];
            $importe_total += $imp_factura;
            $facturas_a_domiciliar_label .= $factura['num_factura'].'/'.$factura['periodo'].';';
        }

        $descripcion_importe = 'XXXXXXXXXXXXXXXX: '.str_replace('.', ',', $importe_total).' Euros';
        $descripcion_facturas = '(Facturas: '.$facturas_a_domiciliar_label.')';
        $concepto_domiciliacion = $descripcion_importe.' '.$descripcion_facturas;

        $referencia_mandato_sufijo = $this->addZerosBefore($cliente['id'], 12);
        $referencia_mandato = $this->mandate_signature_date.$referencia_mandato_sufijo;
        $referencia_adeudo = $referencia_mandato.$this->addZerosBefore($this->debit_order_id, 10); // ID cliente + debit_order_id = clave unica

        // Campo 1
        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        // Campo 2
        $str .= $this->adjustLength($this->book_version, 5); // Ajustar longitud de dato a longitud máxima posible
        // Campo 3
        $str .= $this->adjustLength($numero_dato, 3); // Ajustar longitud de dato a longitud máxima posible
        // Campo 4
        $str .= $this->adjustLength($referencia_adeudo, 35); // Ajustar longitud de dato a longitud máxima posible
        // Campo 5
        $str .= $this->adjustLength($referencia_mandato, 35); // Ajustar longitud de dato a longitud máxima posible

        // Campo 6
        $secuencia_adeudo = ($this->customerHasClosedBills($cliente['id'])) ? 'RCUR' : 'FRST';
        $str .= $this->adjustLength($secuencia_adeudo, 4); // Ajustar longitud de dato a longitud máxima posible

        // Campo 7
        $categoria_proposito = ''; // Campo Opcional
        $str .= $this->adjustLength($categoria_proposito, 4); // Ajustar longitud de dato a longitud máxima posible
        // Campo 8
        if(strpos($importe_total, '.') == false){
            $importe_total = (strpos($importe_total, '.') == false) ? $importe_total.'.00' : $importe_total;
        }else{
            $importe_total_arr = explode('.', $importe_total);
            $decimales = $importe_total_arr[1];
            $data_length_decimales = strlen($decimales);
            if($data_length_decimales == 1){
                $importe_total .= '0';
            }elseif($data_length_decimales > 2){
                $decimales = substr($decimales, 0, 2);
                $importe_total = $importe_total_arr[0].'.'.$decimales;
            }
        }
        $importe_total_label = number_format($importe_total, 2, '.', ',');
        $importe_total_label = str_replace('.', '', $importe_total_label);
        $importe_total_label = str_replace(',', '', $importe_total_label);
        $limite = 11 - strlen($importe_total_label);
        $importe_adeudo_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $importe_adeudo_prefijo .= '0';
        }
        $importe_adeudo_prefijo .= $importe_total_label;
        $str .= $this->adjustLength($importe_adeudo_prefijo, 11); // Ajustar longitud de dato a longitud máxima posible

        $this->total_debits++;
        $this->total_debits_amount += $importe_total;

        // Campo 9
        $str .= $this->adjustLength($this->mandate_signature_date, 8); // Ajustar longitud de dato a longitud máxima posible
        // Campo 10
        $swift_bic = $cliente['swiftbic'];
        //$str .= $this->adjustLength($swift_bic, 11, 'X'); // Ajustar longitud de dato a longitud máxima posible
        $str .= '           '; // 11 espacios en blanco. valor no requerido y al incluirse genera fallo feb-2020
        // Campo 11
        if(strlen($cliente['titular_do']) > 0){
            //$cliente_nombre = utf8_decode($cliente['titular_do']);
            $cliente_nombre = $this->replaceBadCharacters(utf8_decode($cliente['titular_do']));
        }elseif(strlen($cliente['titular_cu']) > 0){
            //$cliente_nombre = utf8_decode($cliente['titular_cu']);
            $cliente_nombre = $this->replaceBadCharacters(utf8_decode($cliente['titular_cu']));
        }else{
            //$cliente_nombre = utf8_decode($cliente['nombre']);
            $cliente_nombre = $this->replaceBadCharacters(utf8_decode($cliente['nombre']));
        }
        $cliente_nombre = strtoupper($cliente_nombre);
        $str .= $this->adjustLength($cliente_nombre, 70); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 12 - Si la dirección es mas larga del limite no se incluye ya que es opcional
        $cliente_direccion = (strlen($cliente['calle']) <= 50) ? utf8_decode($cliente['calle']) : '';
        $cliente_direccion = strtoupper($cliente_direccion);
        $str .= $this->adjustLength($cliente_direccion, 50); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 13
        $cliente_cp_ciudad = $cliente['cp'].' - '.utf8_decode($cliente['ciudad']);
        $cliente_cp_ciudad = strtoupper($cliente_cp_ciudad);
        $str .= $this->adjustLength($cliente_cp_ciudad, 50); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 14
        $cliente_provincia = utf8_decode($cliente['provincia']);
        $cliente_provincia = strtoupper($cliente_provincia);
        $str .= $this->adjustLength($cliente_provincia, 40); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 15
        $str .= $this->adjustLength($this->country_code, 2); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 16
        $tipo_deudor = 2;
        $str .= $this->adjustLength($tipo_deudor, 1); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 17
        $identificador_deudor_prefijo = 'J';
        $cliente_nif = $cliente['nif'];
        //$identificador_deudor = $identificador_deudor_prefijo.$cliente_nif; // Eliminamos el identificador deudor prefijo y cliente nif del dato por fallo a partir de noviembre 2024
        $identificador_deudor = ''; // Campo opcional a partir de novimebre 2024
        $str .= $this->adjustLength($identificador_deudor, 36); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 18 - Opcional - Vacio
        $identificador_deudor_otro = '';
        $str .= $this->adjustLength($identificador_deudor_otro, 35); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 19
        $identificacion_cuenta_deudor = 'A'; // A = IBAN
        $str .= $this->adjustLength($identificacion_cuenta_deudor, 1); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 20
        $cuenta_deudor = $cliente['iban'].$cliente['banco'].$cliente['sucursal'].$cliente['dc'].$cliente['cuenta'];
        $str .= $this->adjustLength($cuenta_deudor, 34); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 21
        $valor = '';
        $str .= $this->adjustLength($valor, 4); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 22
        $str .= $this->adjustLength($concepto_domiciliacion, 140); // Ajustar cantidad de espacios que puede ocupar el dato
        // Campo 23
        $valor = '';
        $str .= $this->adjustLength($valor, 19); // Ajustar cantidad de espacios que puede ocupar el dato
        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres
        $str = $this->replaceBadCharacters($str);

        return $str;
    }

    // Registro de total acreedor n fecha n ( N1914 pag 17 )
    private function getAntepenultimateRowDoc(){
        $str = '';
        $codigo_registro = '04';

        // Campo 1
        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        // Campo 2
        $conductor_identification = $this->country_code.$this->conductor_identification.$this->nif;
        $str .= $this->adjustLength($conductor_identification, 35); // Ajustar longitud de dato a longitud máxima posible
        // Campo 3
        $str .= $this->adjustLength($this->debit_date_label, 8); // Ajustar longitud de dato a longitud máxima posible
        // Campo 4
        $importe_total = number_format($this->total_debits_amount, 2, '.', ',');
        $importe_total_label = str_replace('.', '', $importe_total);
        $importe_total_label = str_replace(',', '', $importe_total_label);

        $limite = 17 - strlen($importe_total_label);
        $importe_adeudo_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $importe_adeudo_prefijo .= '0';
        }
        $importe_adeudo_prefijo .= $importe_total_label;
        $str .= $this->adjustLength($importe_adeudo_prefijo, 17); // Ajustar longitud de dato a longitud máxima posible
        // Campo 5
        $limite = 8 - strlen($this->total_debits);
        $total_cargos_domiciliados_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_cargos_domiciliados_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_cargos_domiciliados_prefijo.$this->total_debits, 8); // Ajustar longitud de dato a longitud máxima posible
        // Campo 6
        $total_registros_acreedor = $this->total_debits + 2;
        $limite = 10 - strlen($total_registros_acreedor);
        $total_registros_acreedor_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_registros_acreedor_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_registros_acreedor_prefijo.$total_registros_acreedor, 10); // Ajustar longitud de dato a longitud máxima posible

        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres

        return $str;
    }
    // Registro de total acreedor n
    private function getPenultimateRowDoc(){
        $str = '';
        $codigo_registro = '05';
        // Campo 1
        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        // Campo 2
        $conductor_identification = $this->country_code.$this->conductor_identification.$this->nif;
        $str .= $this->adjustLength($conductor_identification, 35); // Ajustar longitud de dato a longitud máxima posible
        // Campo 3
        $importe_total = number_format($this->total_debits_amount, 2, '.', ',');
        $importe_total_label = str_replace('.', '', $importe_total);
        $importe_total_label = str_replace(',', '', $importe_total_label);
        $limite = 17 - strlen($importe_total_label);
        $importe_adeudo_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $importe_adeudo_prefijo .= '0';
        }
        $str .= $this->adjustLength($importe_adeudo_prefijo.$importe_total_label, 17); // Ajustar longitud de dato a longitud máxima posible
        // Campo 4
        $limite = 8 - strlen($this->total_debits);
        $total_cargos_domiciliados_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_cargos_domiciliados_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_cargos_domiciliados_prefijo.$this->total_debits, 8); // Ajustar longitud de dato a longitud máxima posible
        // Campo 5
        $total_registros_acreedor = $this->total_debits + 3;
        $limite = 10 - strlen($total_registros_acreedor);
        $total_registros_acreedor_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_registros_acreedor_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_registros_acreedor_prefijo.$total_registros_acreedor, 10); // Ajustar longitud de dato a longitud máxima posible

        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres

        return $str;
    }
    // Registro de total fichero
    private function getFinalRowDoc(){
        $str = '';
        $codigo_registro = '99';
        // Campo 1
        $str .= $this->adjustLength($codigo_registro, 2); // Ajustar longitud de dato a longitud máxima posible
        // Campo 2
        $importe_total = number_format($this->total_debits_amount, 2, '.', ',');
        $importe_total_label = str_replace('.', '', $importe_total);
        $importe_total_label = str_replace(',', '', $importe_total_label);
        $limite = 17 - strlen($importe_total_label);
        $importe_adeudo_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $importe_adeudo_prefijo .= '0';
        }
        $str .= $this->adjustLength($importe_adeudo_prefijo.$importe_total_label, 17); // Ajustar longitud de dato a longitud máxima posible
        // Campo 3
        $limite = 8 - strlen($this->total_debits);
        $total_cargos_domiciliados_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_cargos_domiciliados_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_cargos_domiciliados_prefijo.$this->total_debits, 8); // Ajustar longitud de dato a longitud máxima posible
        // Campo 4
        $total_registros_acreedor = $this->total_debits + 5;
        $limite = 10 - strlen($total_registros_acreedor);
        $total_registros_acreedor_prefijo = '';
        for($i=0;$i<$limite;$i++){
            $total_registros_acreedor_prefijo .= '0';
        }
        $str .= $this->adjustLength($total_registros_acreedor_prefijo.$total_registros_acreedor, 10); // Ajustar longitud de dato a longitud máxima posible

        $str = $this->adjustLength($str); // Ajustar longitud de linea a 600 caracteres

        return $str;
    }
}
?>
