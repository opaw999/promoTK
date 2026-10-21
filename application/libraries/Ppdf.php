 <?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    require_once dirname(__FILE__) . '../../third_party/tcpdf/tcpdf.php';
    //  require_once dirname(__FILE__) . '../../third_party/tcpdf/example/tcpdf_include.php';

    class Ppdf extends TCPDF
    {
        function __construct()
        {
            parent::__construct();
        }

        

    }

/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */
