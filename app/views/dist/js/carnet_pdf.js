document.getElementById('descargar').addEventListener('click', async () => {
    const carnet = document.getElementById('carnet');
    const canvas = await html2canvas(carnet);
    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [95, 95] // tamaño tipo carnet
    });
    pdf.addImage(imgData, 'PNG', 0, 0, 95, 95);
    pdf.save('carnet_' + datosPersona.nombres + '_' + datosPersona.apellidos + '.pdf');
});
