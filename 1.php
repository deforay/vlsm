<?php
class myConfiguration
{
    
    public function __construct($sampleIdCol = null,$sampleIdRow=null,$logValueCol = null,$logValueRow=null,$absoluteValueCol = null,$absoluteValueRow=null, $textValueCol = null,$textValueRow=null,$seperator = null,$logAndAbsoluteValSameColumn)
    {
        $this->sampleIdCol = $sampleIdCol;
        $this->sampleIdRow = $sampleIdRow;
        $this->logValueCol = $logValueCol;
        $this->logValueRow = $logValueRow;
        $this->absoluteValueCol = $absoluteValueCol;
        $this->absoluteValueRow = $absoluteValueRow;
        $this->textValueCol = $textValueCol;
        $this->textValueRow = $textValueRow;
        $this->seperator = $seperator;
        $this->logAndAbsoluteValSameColumn = $logAndAbsoluteValSameColumn;
        
    }
    public function getConfigurationVal(){
        return array(
            'sampleIdCol'=>$this->sampleIdCol,
            'sampleIdRow'=>$this->sampleIdRow,
            'logValueCol'=>$this->logValueCol,
            'logValueRow'=>$this->logValueRow,
            'absoluteValueCol'=>$this->absoluteValueCol,
            'absoluteValueRow'=>$this->absoluteValueRow,
            'textValueCol'=>$this->textValueCol,
            'textValueRow'=>$this->textValueRow,
            'seperator'=>$this->seperator,
            'logAndAbsoluteValSameColumn'=>$this->logAndAbsoluteValSameColumn
        );
        
    }
}

$sampleIdCol='E';
$sampleIdRow='2';
$logValueCol='I';
$logValueRow='I2';
$absoluteValueCol='';
$absoluteValueRow='';
$textValueCol='';
$textValueRow='';
$seperator='(';
$logAndAbsoluteValSameColumn='yes';

$myConf = new myConfiguration($sampleIdCol,$sampleIdRow,$logValueCol,$logValueRow,$absoluteValueCol,$absoluteValueRow,$textValueCol,$textValueRow,$seperator,$logAndAbsoluteValSameColumn);

?>