String.prototype.replaceAll = function (search, replacement) {
	var target = this;
	return target.split(search).join(replacement);
};

var available_printers = null;
var selected_category = null;
var default_printer = null;
var selected_printer = null;

// var zebraFormat = "^XA~TA000~JSN^LT0^MNW^MTT^PON^PMN^LH0,0^JMA^PR3,3~SD5^JUS^LRN^CI0^XZ\
// ^XA\
// ^MMT\
// ^PW1200\
// ^LL0600\
// ^LS0\
// ^BY3,3,102^FT167,521^B3B,N,,Y,N\
// ^FD1234567^FS\
// ^BY3,3,102^FT1079,503^B3B,N,,Y,N\
// ^FD1234567^FS\
// ^BY3,3,102^FT784,514^B3B,N,,Y,N\
// ^FD1234567^FS\
// ^BY3,3,102^FT474,513^B3B,N,,Y,N\
// ^FD1234567^FS\
// ^PQ1,0,1,Y^XZ";





var default_mode = true;

function urldecode(str) { if (typeof str != "string") { return str; } return decodeURIComponent(str.replace(/\+/g, ' ')); }

function setup_web_print() {
	$('#printer_select').on('change', onPrinterSelected);
	//showLoading("Loading Printer Information...");
	default_mode = true;
	selected_printer = null;
	available_printers = null;
	selected_category = null;
	default_printer = null;

	BrowserPrint.getDefaultDevice('printer', function (printer) {
		default_printer = printer
		if ((printer != null) && (printer.connection != undefined)) {
			selected_printer = printer;
			var printer_details = $('#printer_details');
			var selected_printer_div = $('#selected_printer');

			selected_printer_div.text("Using Default Printer: " + printer.name);
			hideLoading();
			printer_details.show();
			$('#print_form').show();

		}
		BrowserPrint.getLocalDevices(function (printers) {
			available_printers = printers;
			var sel = document.getElementById("printers");
			var printers_available = false;
			sel.innerHTML = "";
			if (printers != undefined) {
				for (var i = 0; i < printers.length; i++) {
					if (printers[i].connection == 'usb') {
						var opt = document.createElement("option");
						opt.innerHTML = printers[i].connection + ": " + printers[i].uid;
						opt.value = printers[i].uid;
						sel.appendChild(opt);
						printers_available = true;
					}
				}
			}

			if (!printers_available) {
				showErrorMessage("No Zebra Printers could be found!");
				hideLoading();
				$('#print_form').hide();

			}
			else if (selected_printer == null) {
				default_mode = false;
				changePrinter();
				$('#print_form').show();
				hideLoading();
			}
		}, undefined, 'printer');
	},
		function (error_response) {
			showBrowserPrintNotFound();
		});
}
function showBrowserPrintNotFound() {
	showErrorMessage("An error occured while attempting to connect to your Zebra Printer. You may not have Zebra Browser Print installed, or it may not be running. Install Zebra Browser Print, or start the Zebra Browser Print Service, and try again.");

}
function printBarcodeLabel(barcode, facility = '', patientART = '') {
	showLoading("Printing...");
	facility = urldecode(facility);
	checkPrinterStatus(function (text) {
		if (text == "Ready to Print") {
			//selected_printer.send(format_start + bcode + format_end, printComplete, printerError);
			let strToPrint = zebraFormat.replaceAll("1234567", barcode);
			strToPrint = strToPrint.replaceAll("BARCODE", barcode);
			if (facility != '') {
				strToPrint = strToPrint.replaceAll("FACILITY", facility);
			}
			if (patientART != '') {
				strToPrint = strToPrint.replaceAll("PATIENTART", patientART);
			}
			selected_printer.send(strToPrint, printComplete, printerError);
		}
		else {
			printerError(text);
		}
	});
}
function checkPrinterStatus(finishedFunction) {
	selected_printer.sendThenRead("~HQES",
		function (text) {
			let that = this;
			let statuses = [];
			let ok = false;
			let is_error = text.charAt(70);
			let media = text.charAt(88);
			let head = text.charAt(87);
			let pause = text.charAt(84);
			// check each flag that prevents printing
			if (is_error == '0') {
				ok = true;
				statuses.push("Ready to Print");
			}
			if (media == '1')
				statuses.push("Paper out");
			if (media == '2')
				statuses.push("Ribbon Out");
			if (media == '4')
				statuses.push("Media Door Open");
			if (media == '8')
				statuses.push("Cutter Fault");
			if (head == '1')
				statuses.push("Printhead Overheating");
			if (head == '2')
				statuses.push("Motor Overheating");
			if (head == '4')
				statuses.push("Printhead Fault");
			if (head == '8')
				statuses.push("Incorrect Printhead");
			if (pause == '1')
				statuses.push("Printer Paused");

			console.log(text);
			console.log(statuses);

			if ((!ok) && (statuses.Count == 0))
				statuses.push("Error: Unknown Error");
			finishedFunction(statuses.join());
		}, printerError);
}
function hidePrintForm() {
	$('#print_form').hide();
}
function showPrintForm() {
	$('#print_form').show();
}
function showLoading(text) {
	$('#loading_message').text(text);
	$('#printer_data_loading').show();
	hidePrintForm();
	$('#printer_details').hide();
	$('#printer_select').hide();
}
function printComplete() {
	hideLoading();
	alert("Printing complete");
}
function hideLoading() {
	$('#printer_data_loading').hide();
	if (default_mode == true) {
		showPrintForm();
		$('#printer_details').show();
	}
	else {
		$('#printer_select').show();
		showPrintForm();
	}
}
function changePrinter() {
	default_mode = false;
	selected_printer = null;
	$('#printer_details').hide();
	if (available_printers == null) {
		showLoading("Searching for Compatible Zebra Printers...Please wait");
		$('#print_form').hide();
		setTimeout(changePrinter, 200);
		return;
	}
	$('#printer_select').show();
	onPrinterSelected();

}
function onPrinterSelected() {
	const printersElement = $('#printers')[0];

	if (printersElement) {
		const selectedPrinter = available_printers[printersElement.selectedIndex];
		// Use selectedPrinter as needed
	} else {
		console.error('Printers element not found');
	}
}
function showErrorMessage(text) {
	$('#main').hide();
	$('#error_div').show();
	$('#error_message').html(text);
}
function printerError(text) {
	showErrorMessage("An error occurred while printing. Please try again." + text);
}
function trySetupAgain() {
	$('#main').show();
	$('#error_div').hide();
	setup_web_print();
	//hideLoading();
}


$(document).ready(function () {
	setup_web_print();
});
