/**
 * DataTables Initialization - Global Script
 * Automatically initializes DataTables on tables with specific classes
 */

(function() {
    'use strict';

    // Default DataTables configuration
    const defaultConfig = {
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _TOTAL_ total entries)",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        order: [[0, 'desc']] // Default sort by first column descending
    };

    // Configuration for tables with export buttons
    const exportConfig = {
        ...defaultConfig,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12 col-md-12"B>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-primary',
                text: '<i class="fas fa-copy"></i> Copy'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-success',
                text: '<i class="fas fa-file-csv"></i> CSV'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-success',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-danger',
                text: '<i class="fas fa-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-info',
                text: '<i class="fas fa-print"></i> Print'
            }
        ]
    };

    /**
     * Initialize DataTables on page load
     */
    function initializeDataTables() {
        // Initialize basic DataTables
        if ($.fn.DataTable) {
            // Tables with class 'datatable'
            $('.datatable').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable(defaultConfig);
                }
            });

            // Tables with class 'datatable-export' (with export buttons)
            $('.datatable-export').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable(exportConfig);
                }
            });

            // Tables with class 'datatable-simple' (minimal features)
            $('.datatable-simple').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        responsive: true,
                        pageLength: 10,
                        searching: true,
                        paging: true,
                        info: true
                    });
                }
            });
        }
    }

    /**
     * Reinitialize DataTables (useful for dynamic content)
     */
    window.reinitializeDataTables = function() {
        // Destroy existing DataTables
        if ($.fn.DataTable) {
            $('.datatable, .datatable-export, .datatable-simple').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
            });
        }
        
        // Reinitialize
        initializeDataTables();
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        initializeDataTables();
    });

    // Also initialize after AJAX content loads
    $(document).ajaxComplete(function() {
        setTimeout(initializeDataTables, 100);
    });

})();
