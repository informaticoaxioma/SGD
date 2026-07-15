<?php
/**
 * Description of DocumentoDetalle
 *
 * @author vfernandez
 */
class DocumentoDetalle {
    private $documento;
    private $detalle;
    
    function __construct($documento, $detalle) {
        $this->documento = $documento;
        $this->detalle = $detalle;
    }

    
    function getDocumento() {
        return $this->documento;
    }

    function getDetalle() {
        return $this->detalle;
    }

    function setDocumento($documento) {
        $this->documento = $documento;
    }

    function setDetalle($detalle) {
        $this->detalle = $detalle;
    }


}
