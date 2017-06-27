function urldecode(str) { if (typeof str != "string") { return str; } return decodeURIComponent(str.replace(/\+/g, ' ')); }

function printBarcodeLabel(barcode,facility){
        
           
            $.blockUI();
            dymo.label.framework.trace = 1; //true
            facility = urldecode(facility);

            try{
                // open label
               var labelXml  = '<?xml version="1.0" encoding="utf-8"?>\
<DieCutLabel Version="8.0" Units="twips">\
	<PaperOrientation>Landscape</PaperOrientation>\
	<Id>Small30345</Id>\
	<IsOutlined>false</IsOutlined>\
	<PaperName>30345 3/4 in x 2-1/2 in</PaperName>\
	<DrawCommands>\
		<RoundRectangle X="0" Y="0" Width="1080" Height="3600" Rx="180" Ry="180" />\
	</DrawCommands>\
	<ObjectInfo>\
		<TextObject>\
			<Name>TEXT</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName />\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>True</IsVariable>\
			<GroupID>-1</GroupID>\
			<IsOutlined>False</IsOutlined>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<VerticalAlignment>Middle</VerticalAlignment>\
			<TextFitMode>ShrinkToFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String xml:space="preserve">Clinic - ABC coll Date 12/Jan/2017</String>\
					<Attributes>\
						<Font Family="Arial" Size="9" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="470.000000000001" Y="716" Width="2940" Height="282" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<BarcodeObject>\
			<Name>BARCODE</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName />\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>True</IsVariable>\
			<GroupID>-1</GroupID>\
			<IsOutlined>False</IsOutlined>\
			<Text>1234567890AB</Text>\
			<Type>Code39</Type>\
			<Size>Small</Size>\
			<TextPosition>Top</TextPosition>\
			<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
			<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
			<TextEmbedding>None</TextEmbedding>\
			<ECLevel>0</ECLevel>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
		</BarcodeObject>\
		<Bounds X="326" Y="127.000000000001" Width="3187" Height="518.4" />\
	</ObjectInfo>\
</DieCutLabel>';
                
                      var label = dymo.label.framework.openLabelXml(labelXml);
  
                      // set label text
                      if(labelXml.indexOf("<Name>BARCODE</Name>") !== -1){
                        label.setObjectText('BARCODE', barcode);
                      }
                      if(labelXml.indexOf("<Name>TEXT</Name>") !== -1){
                        label.setObjectText('TEXT', barcode);
                      }
                      
                      
                      // select printer to print on
                      // for simplicity sake just use the first LabelWriter printer
                      var printers = dymo.label.framework.getPrinters();
                      if (printers.length === 0)
                          throw "No DYMO printers are installed. Install DYMO printers.";
  
                      var printerName = "";
                      for (var i = 0; i < printers.length; ++i)
                      {
                          var printer = printers[i];
                          if (printer.printerType == "LabelWriterPrinter")
                          {
                              printerName = printer.name;
                              break;
                          }
                      }
  
                      if (printerName === "")
                          throw "No LabelWriter printers found. Install LabelWriter printer";
  
                      // finally print the label
                      label.print(printerName);
                
              
            }
            catch(e){
                alert(e.message || e);
            }
            $.unblockUI();
        
    };

