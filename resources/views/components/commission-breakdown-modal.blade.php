<!-- Commission Details Modal -->
<div class="modal fade" id="commissionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="commissionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commissionDetailsModalLabel">Commission Breakdown</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commissionDetailsContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printCommissionDetails()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function printCommissionDetails() {
    const content = document.getElementById('commissionDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Commission Breakdown</title>
                <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .table { margin-top: 20px; }
                    @media print {
                        .btn { display: none; }
                    }
                </style>
            </head>
            <body>
                <h2>Commission Breakdown Report</h2>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
                ${content}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>