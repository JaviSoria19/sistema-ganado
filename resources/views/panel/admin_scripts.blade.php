<style>
    .info-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .icon-box {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 28px;
    }

    .text-dark-aquamarine {
        color: #20c997 !important;
    }

    .btn-cow-brown{
        background-color: #8B4513;
        color: #fff;
    }
</style>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            @include('components.datatables.datatables_global_properties')
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTableExportButtonsContainer');
        $('#dataTable2').DataTable({
            @include('components.datatables.datatables_global_properties')
            @include('components.datatables.datatables_language_property')
        }).buttons().container().appendTo('#dataTableExportButtonsContainer2');
    });
</script>
