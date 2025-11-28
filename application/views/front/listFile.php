<?php $this->load->view('front/menu');?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#btnAdd").click(function() {
            $("#mainContentDataTable").fadeOut(300, function() {
                $("#mainContentDownloadFile").fadeIn(300);
            });
        });

        $("#btnCancelUpload").click(function() {
            $("#mainContentDownloadFile").fadeOut(300, function() {
                $("#mainContentDataTable").fadeIn(300);
            });
        });
    });

    $(document).ready(function() {

        $(document).on("change", ".slcVessel, .slcVesselType", function() {
            loadCategories();
        });

        $(document).on("change", ".txtCategory", function() {
            loadFileData();
        });

        function loadCategories() {
            var vessel = $(".slcVessel").val();
            var vesselType = $(".slcVesselType").val();

            if (!vessel || !vesselType) {
                $(".txtCategory").html('<option value="">-- Select Category --</option>');
                return;
            }

            $.ajax({
                url: "<?php echo base_url('listFile/getCategories'); ?>",
                type: "GET",
                dataType: "json",
                data: {
                    vessel: vessel,
                    vesselType: vesselType
                },
                success: function(data) {
                    var options = '<option value="">-- Select Category --</option>';
                    $.each(data, function(i, item) {
                        options += '<option value="' + item.value + '">' + item.label +
                            '</option>';
                    });
                    $(".txtCategory").html(options);
                },
                error: function() {
                    $(".txtCategory").html(
                        '<option value="">-- Error loading categories --</option>');
                }
            });
        }

        function loadFileData() {
            var vessel = $(".slcVessel").val();
            var vesselType = $(".slcVesselType").val();
            var cat = $(".txtCategory").val();

            if (!vessel || !vesselType) {
                return;
            }

            $.ajax({
                url: "<?php echo base_url('listFile/getFileData'); ?>",
                type: "POST",
                data: {
                    vessel: vessel,
                    vesselType: vesselType,
                    category: cat
                },
                beforeSend: function() {
                    $("#tbodyFile").html(
                        '<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                },
                success: function(res) {
                    $("#tbodyFile").html(res);
                },
                error: function() {
                    $("#tbodyFile").html(
                        '<tr><td colspan="6" class="text-center">Error loading data</td></tr>');
                }
            });
        }

        window.resetFilters = function() {
            $('.slcVessel').val('');
            $('.slcVesselType').val('');
            $('.txtCategory').html('<option value="">-- Select Category --</option>');
            $('#tbodyFile').html(
                '<tr><td colspan="6" class="text-center text-muted">Select a vessel & department</td></tr>'
            );
        };
    });

    $(document).on("click", ".btnSaveFile", function(e) {
        var fileId = $(this).data("id");

        $.ajax({
            url: "<?php echo base_url('listFile/saveToListFile'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                idFile: fileId
            },
            success: function(res) {
                alert(res.message);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
                console.log(xhr.responseText);
            }
        });

    });

    function openUploadModal(id) {
        $("#idFile").val(id);
        $("#modalUploadFile").modal("show");
    }

    $(document).ready(function() {
        $("#formUploadFile").on("submit", function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "<?php echo base_url('listFile/saveToListFile'); ?>",
                type: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#idLoadingSpinner").fadeIn(200);
                },
                success: function(res) {
                    $("#idLoadingSpinner").fadeOut(200);

                    if (res.status === "success") {
                        alert(res.message);
                        $("#modalUploadFile").modal("hide");
                        location.reload();
                    } else {
                        alert(res.message || "⚠️ Terjadi kesalahan.");
                    }
                },
                error: function(xhr, status, error) {
                    $("#idLoadingSpinner").fadeOut(200);
                    console.error("Upload error:", error);
                    alert("❌ Error uploading file, please try again!");
                },
                complete: function() {
                    $("#btnUpload").prop("disabled", false).text("Upload");
                }
            });
        });
    });

    $(document).ready(function() {
        getData("");

        function getData(txtSearch) {
            $.ajax({
                url: "<?php echo base_url('listFile/getData/search'); ?>",
                type: "POST",
                data: {
                    txtSearch: txtSearch
                },
                dataType: "json",
                beforeSend: function() {
                    $("#idTbodyHistory").html(
                        "<tr><td colspan='7' style='text-align:center;'>Loading...</td></tr>"
                    );
                },
                success: function(res) {
                    if (res.trNya && res.trNya !== "") {
                        $("#idTbodyHistory").html(res.trNya);
                    } else {
                        $("#idTbodyHistory").html(
                            "<tr><td colspan='7' style='text-align:center;'>No data found</td></tr>"
                        );
                    }
                },
                error: function() {
                    $("#idTbodyHistory").html(
                        "<tr><td colspan='7' style='text-align:center;color:red;'>Error loading data</td></tr>"
                    );
                }
            });
        }

        $("#btnSearch").click(function() {
            var txtSearch = $("#txtSearch").val();
            getData(txtSearch);
        });

        $("#txtSearch").keypress(function(e) {
            if (e.which === 13) {
                var txtSearch = $(this).val();
                getData(txtSearch);
            }
        });
    });
    </script>
</head>

<body style="background:#f5f7fa;font-family:'Segoe UI',Tahoma,sans-serif;">
    <section id="container" style="position:relative;">
        <div id="idLoadingSpinner" style="
                    display:none;
                    position:fixed;
                    top:0; left:0;
                    width:100%; height:100%;
                    background:rgba(0,0,0,0.6);
                    z-index:9999;
                    justify-content:center;
                    align-items:center;
                    flex-direction:column;
                    ">

            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 50 50"
                style="margin:auto; background:none; display:block;">
                <circle cx="25" cy="25" r="20" fill="none" stroke="white" stroke-width="5" stroke-linecap="round"
                    stroke-dasharray="31.4 31.4" transform="rotate(-90 25 25)">
                    <animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s"
                        repeatCount="indefinite" />
                </circle>
            </svg>

            <p style="margin-top:20px; font-size:16px; color:#fff; font-weight:bold; text-align:center;">
                ⏳ Please wait... Processing data
            </p>
        </div>
        <main id="main-content" class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <section class="wrapper site-min-height"
                style="min-height:400px; background:#f9fafb; padding:28px; position:relative;">

                <div id="mainContentDataTable"
                    style="border:1px solid  border-radius:10px; background:#ffffff; box-shadow:0 2px 8px rgba(0,0,0,0.04); margin-bottom:28px; overflow:hidden;">

                    <!-- Header -->
                    <div
                        style="display:flex; justify-content:space-between; align-items:center;
                        padding:16px 20px; background:linear-gradient(90deg,#f9fafb,#f3f4f6); border-bottom:1px solid ">
                        <h4 style="margin:0; font-weight:600; color:#111827; font-size:17px;
                            display:flex; align-items:center; letter-spacing:0.3px;">
                            <i class="fa fa-folder-open text-primary" style="margin-right:8px;"></i> Form SMS
                        </h4>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button type="button" id="btnAdd" class="btn btn-primary btn-sm"
                                style="background:#2563eb; border:none; padding:8px 14px; border-radius:6px;
                            color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-download" style="margin-right:6px;"></i>Download
                            </button>
                            <input type="text" id="txtSearch" placeholder="Search file..." style="border:1px solid #d1d5db; border-radius:6px; padding:8px 12px;
                             width:220px; font-size:14px; transition:all 0.2s;">
                            <button id="btnSearch"
                                style="background:#059669; border:none; padding:8px 14px; border-radius:6px;
                             color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-search" style="margin-right:6px;"></i>Search
                            </button>
                        </div>
                    </div>

                    <div style="padding:18px;">
                        <div style="overflow-x:auto; border-radius:8px; ">
                            <table style="width:100%; border-collapse:collapse; font-family:'Inter','Segoe UI',sans-serif;x
                                font-size:14px; color:white;">
                                <thead>
                                    <tr style="background-color:#7192AF; text-align:left;">
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            No</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Username</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Vessel</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            File Name</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Vessel Typec</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            File</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Category</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Remarks</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Download Time</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Upload Time</th>
                                        <th
                                            style="padding:12px 14px; border-bottom:2px solid  font-weight:600; text-align:center;">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody id="idTbodyHistory">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="mainContentDownloadFile" style="display:none; border:1px solid  
                    border-radius:10px; background:#ffffff; margin-bottom:28px; 
                    box-shadow:0 4px 10px rgba(0,0,0,0.05); overflow:hidden;">

                    <div
                        style="display:flex; justify-content:space-between; align-items:center;
                         padding:16px 20px; background:linear-gradient(90deg,#f9fafb,#f3f4f6); border-bottom:1px solid ">
                        <h4 style="margin:0; font-weight:600; color:#111827; font-size:17px;
                            display:flex; align-items:center; letter-spacing:0.3px;">
                            <i class="fa fa-download" style="color:#2563eb; margin-right:8px;"></i> Download File Form
                            SMS
                        </h4>
                        <button type="button" id="btnCancelUpload"
                            style="background:#ef4444; border:none; padding:8px 14px; border-radius:6px;
                            color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                            <i class="fa fa-times" style="margin-right:6px;"></i>Cancel
                        </button>
                    </div>

                    <div style="padding:20px; background:#ffffff; border-bottom:1px solid #f3f4f6;">
                        <div style="display:flex; gap:20px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:220px;">
                                <label
                                    style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">Nama
                                    Kapal</label>
                                <select name="slcVessel" class="slcVessel" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;
                                    background:#f9fafb; font-size:14px;">
                                    <?php echo $vessel; ?>
                                </select>
                            </div>
                            <div style="min-width:200px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Vessel Type</label>
                                <select name="slcVesselType" class="slcVesselType"
                                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                                    <?php echo $vesselType; ?>
                                </select>
                            </div>
                            <div style="flex:1; min-width:200px;">
                                <label
                                    style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">Category</label>
                                <select name="txtCategory" class="txtCategory" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;
                                    background:#f9fafb; font-size:14px;">
                                    <option value="">-- Select Category --</option>
                                </select>
                            </div>
                            <div style="flex:1; min-width:140px; display:flex; align-items:flex-end;">
                                <button type="button" onclick="resetFilters()" style="background:#6b7280; border:none; padding:10px 16px; border-radius:8px;
                                    color:white; font-size:14px; font-weight:500; cursor:pointer; width:100%;">
                                    <i class="fa fa-undo" style="margin-right:6px;"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="padding:20px;">
                        <div style="overflow-x:auto; border-radius:8px;">
                            <table style="width:100%; border-collapse:collapse; font-family:'Inter','Segoe UI',sans-serif;
                                    font-size:14px; color:white; text-align:center;">
                                <thead>
                                    <tr style="background-color:#7192AF;">
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            No</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Vessel</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Vessel Type</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Category</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Filename</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            File</th>
                                        <th style="padding:12px 14px; border-bottom:2px solid  font-weight:600;">
                                            Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyFile">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </section>
</body>

</html>

<div class="modal fade" id="modalUploadFile" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formUploadFile" enctype="multipart/form-data">
                <div class="modal-header" style="background-color:#7192AF;">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idFile" id="idFile">
                    <div class="form-group">
                        <label for="fileUpload">Choose file</label>
                        <input type="file" name="fileUpload" id="fileUpload" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Upload</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>