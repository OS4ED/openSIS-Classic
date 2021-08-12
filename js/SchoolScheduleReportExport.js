function exportAsSpreadsheet(exportFileName) {
    $("#schedule-markup").table2excel({
        exclude: ".noExl",
        filename: exportFileName
    });
}

function exportAsPDF(exportFileName) {
    var doc = new jsPDF('l', 'pt', 'a3');
    doc.autoTable({  
        html: '#schedule-markup',   
        theme: 'grid',
        headStyles: { fillColor: [154, 154, 154] },
        bodyStyles: { textColor: [0, 0, 0], cellWidth: 'auto' }
    })  
    doc.save(exportFileName + '.pdf');
}

function exportAsPrint(exportFileName) {
    var printContents = document.getElementById("ssr-table").innerHTML;
    var originalContents = document.body.innerHTML;

    document.title = exportFileName;
    document.body.innerHTML = printContents;

    window.print();

    document.body.innerHTML = originalContents;
}