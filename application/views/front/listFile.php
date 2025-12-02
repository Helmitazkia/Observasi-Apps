<?php $this->load->view('front/menu');?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $("#mainContentDataTable").fadeOut(300, function () {
                    $("#mainContentDownloadFile").fadeIn(300);
                });
            });

            $("#btnCancelUpload").click(function () {
                $("#mainContentDownloadFile").fadeOut(300, function () {
                    $("#mainContentDataTable").fadeIn(300);
                });
            });


            $("#btn-form-upload").click(function () {
                $("#mainContentDataTable").fadeOut(300, function () {
                    $("#content-file-upload").fadeIn(300);
                });
            });

            $

                ("#btn-form-cancel-upload").click(function () {
                    $("#content-file-upload").fadeOut(300, function () {
                        $("#mainContentDataTable").fadeIn(300);
                    });
                });
        });



        var tsVessel;
        var tsVesselType;
        var tsCategory;
        var tsVesselUpload;
        var tsVesselTypeUpload;
        var tsCategoryUpload;
        var slcDepartementUpload
        var slcFileNameUpload

        $(document).ready(function () {

            tsVessel = new TomSelect(".slcVessel", {
                create: false,
                searchField: ["text"]
            });

            tsVesselType = new TomSelect(".slcVesselType", {
                create: false,
                searchField: ["text"]
            });

            tsCategory = new TomSelect(".txtCategory", {
                create: false,
                allowEmptyOption: true,
                placeholder: "Select Category"
            });

            tsVesselUpload = new TomSelect(".slcVesselUpload", {
                create: false,
                searchField: ["text"]
            });

            tsVesselTypeUpload = new TomSelect(".slcVesselTypeUpload", {
                create: false,
                searchField: ["text"]
            });

            tsCategoryUpload = new TomSelect(".txtCategoryUpload", {
                create: false,
                allowEmptyOption: true,
                placeholder: "Select Category"
            });

            slcDepartementUpload = new TomSelect(".slcDepartementUpload", {
                create: false,
                searchField: ["text"]
            });
            slcFileNameUpload = new TomSelect(".slcFileNameUpload", {
                create: false,
                allowEmptyOption: true,
                placeholder: "Select Option File Name"
            });

            $(document).on("change", ".slcVessel, .slcVesselType", function () {
                loadCategories();
            });

            $(document).on("change", ".slcVesselUpload, .slcVesselTypeUpload", function () {
                $(".txtCategoryUpload").each(function () {
                    if (this.selectize) {
                        this.selectize.destroy();
                    }
                });
                loadCategoriesUpload();
            });

            $(document).on("change", ".txtCategory", function () {
                loadFileData();

            });

            $(document).on("change", ".txtCategoryUpload", function () {
                $(".slcFileNameUpload").each(function () {
                    if (this.selectize) {
                        this.selectize.destroy();
                    }
                });
                loadfilenameOption();

            });





        });


        $(document).on("click", ".btnSaveFile", function (e) {
            var fileId = $(this).data("id");

            $.ajax({
                url: "<?php echo base_url('listFile/saveToListFile'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    idFile: fileId
                },
                success: function (res) {
                    alert(res.message);
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.log("Error:", error);
                    console.log(xhr.responseText);
                }
            });

        });

        function openUploadModal(id) {
            $("#idFile").val(id);
            $("#modalUploadFile").modal("show");
        }

        $(document).ready(function () {
            $("#formUploadSMS").on("submit", function (e) {
                e.preventDefault();



                var formData = new FormData(this);
                console.log("Form Data prepared:", formData);
                // var fileUpload = $("#fileUpload")[0].files[0];
                // console.log("File selected:", fileUpload);

                // // Kalau mau re-append file (opsional, tidak wajib)
                // formData.append("fileUpload", fileUpload);
                // console.log(formData,fileUpload);

                // return false;

                $.ajax({
                    url: "<?php echo base_url('listFile/saveToListFile'); ?>",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $("#idLoadingSpinner").fadeIn(200);
                    },
                    success: function (res) {
                        $("#idLoadingSpinner").fadeOut(200);

                        if (res.status === "success") {
                            alert(res.message);
                            //     $("#modalUploadFile").modal("hide");
                            location.reload();
                        } else {
                            alert(res.message || "Terjadi kesalahan.");
                        }
                    },
                    error: function (xhr, status, error) {
                        $("#idLoadingSpinner").fadeOut(200);
                        console.error("Upload error:", error);
                        alert("Error uploading file, please try again!");
                    }
                    // ,
                    // complete: function () {
                    //     $("#btnUpload").prop("disabled", false).text("Upload");
                    // }
                });
            });
        });

        $(document).ready(function () {
            getData("", "", "");

            $("#btnSearch").click(function () {
                FilterData();
            });

            $("#txtSearch").keypress(function (e) {
                if (e.which === 13) {
                    getsearchOnEnter();
                }
            });

        });

        function getData(txtSearch, startDate, endDate) {
            $.ajax({
                url: "<?php echo base_url('listFile/getData/search'); ?>",
                type: "POST",
                data: {
                    txtSearch: txtSearch,
                    startDate: startDate,
                    endDate: endDate
                },
                dataType: "json",
                beforeSend: function () {
                    $("#idTbodyHistory").html(
                        "<tr><td colspan='7' style='text-align:center;'>Loading...</td></tr>"
                    );
                },
                success: function (res) {
                    // console.log(res)
                    if (res.trNya && res.trNya !== "") {
                        $("#idTbodyHistory").html(res.trNya);
                    } else {
                        $("#idTbodyHistory").html(
                            "<tr><td colspan='7' style='text-align:center;'>No data found</td></tr>"
                        );
                    }
                },
                error: function () {
                    $("#idTbodyHistory").html(
                        "<tr><td colspan='7' style='text-align:center;color:red;'>Error loading data</td></tr>"
                    );
                }
            });
        }


        function FilterData() {
            var txtSearch = $("#txtSearch").val();
            var startDate = $("#startDate").val();
            var endDate = $("#endDate").val();
            getData(txtSearch, startDate, endDate);
        }


        function getsearchOnEnter() {
            var txtSearch = $("#txtSearch").val();
            var startDate = $("#startDate").val();
            var endDate = $("#endDate").val();
            getData(txtSearch, startDate, endDate);
        }

        function resetFilters() {
            tsVessel.clear();
            tsVesselType.clear();
            tsCategory.clear();

            tsCategory.clearOptions();
            tsCategory.addOption({
                value: "",
                text: "Select Category"
            });
            tsCategory.refreshOptions(false);

            $("#tbodyFile").html(
                '<tr><td colspan="6" class="text-center text-muted">Select vessel & type</td></tr>'
            );
        }

        function loadCategories() {
            var vessel = $(".slcVessel").val();
            var vesselType = $(".slcVesselType").val();

            if (!vessel || !vesselType) {

                tsCategory.clearOptions();
                tsCategory.addOption({
                    value: "",
                    text: "Select Category"
                });
                tsCategory.refreshOptions(false);

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
                success: function (data) {
                    console.log("categories data:", data);
                    tsCategory.clearOptions();
                    tsCategory.addOption({
                        value: "",
                        text: "Select Category"
                    });
                    $.each(data, function (i, item) {
                        tsCategory.addOption({
                            value: item.value,
                            text: item.label
                        });
                    });

                    tsCategory.refreshOptions(false);
                },
                error: function () {
                    tsCategory.clearOptions();
                    tsCategory.addOption({
                        value: "",
                        text: "-- Error loading categories --"
                    });
                    tsCategory.refreshOptions(false);
                }
            });
        }

        function loadCategoriesUpload() {
            var slcVesselUpload = $(".slcVesselUpload").val();
            var slcVesselTypeUpload = $(".slcVesselTypeUpload").val();

            if (!slcVesselUpload || !slcVesselTypeUpload) {
                tsCategoryUpload.clearOptions();
                tsCategoryUpload.addOption({
                    value: "",
                    text: "Select Category"
                });
                tsCategoryUpload.refreshOptions(false);
                tsCategoryUpload.setValue("");

                return;
            }

            $.ajax({
                url: "<?php echo base_url('listFile/getCategories'); ?>",
                type: "GET",
                dataType: "json",
                data: {
                    vessel: slcVesselUpload,
                    vesselType: slcVesselTypeUpload
                },
                success: function (data) {
                    tsCategoryUpload.clearOptions();
                    tsCategoryUpload.addOption({
                        value: "",
                        text: "Select Category"
                    });
                    $.each(data, function (i, item) {
                        tsCategoryUpload.addOption({
                            value: item.value,
                            text: item.label
                        });
                    });

                    tsCategoryUpload.refreshOptions(false);
                },
                error: function () {
                    tsCategoryUpload.clearOptions();
                    tsCategoryUpload.addOption({
                        value: "",
                        text: "-- Error loading categories --"
                    });
                    tsCategoryUpload.refreshOptions(false);
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
                beforeSend: function () {
                    $("#tbodyFile").html(
                        '<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                },
                success: function (res) {
                    $("#tbodyFile").html(res);
                },
                error: function () {
                    $("#tbodyFile").html(
                        '<tr><td colspan="6" class="text-center">Error loading data</td></tr>');
                }
            });
        }


        function loadfilenameOption() {
            var slcVesselUpload = $(".slcVesselUpload").val();
            var slcVesselTypeUpload = $(".slcVesselTypeUpload").val();
            var tsCategoryUpload = $(".txtCategoryUpload").val();


            if (!slcVesselUpload || !slcVesselTypeUpload || !tsCategoryUpload) {
                slcFileNameUpload.clearOptions();
                slcFileNameUpload.addOption({
                    value: "",
                    text: "Select File Name"
                });
                slcFileNameUpload.refreshOptions(false);
                return;
            }

            $.ajax({
                url: "<?php echo base_url('listFile/getFileName'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    vessel: slcVesselUpload,
                    vesselType: slcVesselTypeUpload,
                    category: tsCategoryUpload
                },
                success: function (data) {
                    console.log(data);

                    slcFileNameUpload.clearOptions();
                    slcFileNameUpload.addOption({
                        value: "",
                        text: "Select File Name"
                    });

                    $.each(data, function (i, item) {
                        slcFileNameUpload.addOption({
                            value: item.value,
                            text: item.label
                        });
                    });

                    slcFileNameUpload.refreshOptions();
                },
                error: function () {
                    slcFileNameUpload.clearOptions();
                    slcFileNameUpload.addOption({
                        value: "",
                        text: "-- Error loading File Names --"
                    });
                    slcFileNameUpload.refreshOptions();
                }
            });
        }
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
                        padding:10px 20px; background:linear-gradient(90deg,#f9fafb,#f3f4f6); border-bottom:1px solid ">
                        <h4 style="margin:0; font-weight:600; color:#111827; font-size:17px;
                            display:flex; align-items:center; letter-spacing:0.3px;">
                            <i class="fa fa-folder-open text-primary" style="margin-right:8px;"></i> Form SMS
                        </h4>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button type="button" id="btn-form-upload" class="btn btn-primary btn-sm"
                                style="background:#2563eb; border:none; padding:8px 14px; border-radius:60px;
                            color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-upload" style="margin-right:6px;"></i>Upload
                            </button>
                            <button type="button" id="btnAdd" class="btn btn-primary btn-sm"
                                style="background:#2563eb; border:none; padding:8px 14px; border-radius:60px;
                            color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-download" style="margin-right:6px;"></i>Download
                            </button>

                            <input type="text" id="txtSearch" style="border:1px solid #d1d5db; border-radius:30px; padding:8px 12px;
                                font-size:14px; width:200px; " placeholder="Search...">
                            <input type="date" id="startDate" style="border:1px solid #d1d5db; border-radius:30px; padding:2px 12px;
                                font-size:14px; width:150px;">

                            <span style="font-size:14px; color:#6b7280;">To</span>

                            <input type="date" id="endDate" style="border:1px solid #d1d5db; border-radius:30px; padding:2px 12px;
                                font-size:14px; width:150px;">

                            <button id="btnSearch"
                                style="background:#059669; border:none; padding:8px 14px; border-radius:6px;
                             color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer; border-radius:30px">
                                <i class="fa fa-search" style="margin-right:6px;"></i>Search
                            </button>
                        </div>
                    </div>

                    <div style="padding:18px;">
                        <div style="overflow-x:auto; border-radius:8px; ">
                            <table
                                style="width:100%; border-collapse:collapse; border:1px solid white; font-family:'Inter','Segoe UI',sans-serif; font-size:14px; color:white;">
                                <thead>
                                    <!-- Baris 1: Header Utama -->
                                    <tr style="background-color:#7192AF; text-align:left;">
                                        <!-- Kolom dengan rowspan="2" untuk semua kecuali Approval -->
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">No</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Username</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Vessel</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Departement</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">File Name</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">New File</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Category</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Remarks</th>
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Upload Time</th>
                                        <!-- <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle;"
                                            rowspan="2">Download Time</th> -->
                                        <!--                                    
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px solid #333; font-weight:600; vertical-align:middle; text-align:center;"
                                            rowspan="2">Action</th> -->

                                        <!-- Kolom Approval (TANPA rowspan, karena punya sub-header) -->
                                        <th style="padding:12px 14px; border:1px solid white; border-bottom:2px; font-weight:600; text-align:center;"
                                            colspan="4">Approval</th>
                                    </tr>

                                    <!-- Baris 2: Sub-header untuk Approval -->
                                    <tr style="background-color:#7192AF; text-align:center;">
                                        <!-- 12 kolom kosong untuk kolom dengan rowspan="2" di atas -->
                                        <td colspan="12" style="display:none;"></td>

                                        <!-- Sub-kolom Approval -->
                                        <th
                                            style="padding:8px 10px; border:1px solid white; border-bottom:2px solid #333; font-weight:600;">
                                            Master</th>
                                        <th
                                            style="padding:8px 10px; border:1px solid white; border-bottom:2px solid #333; font-weight:600;">
                                            Os</th>
                                        <th
                                            style="padding:8px 10px; border:1px solid white; border-bottom:2px solid #333; font-weight:600;">
                                            Deck</th>
                                        <th
                                            style="padding:8px 10px; border:1px solid white; border-bottom:2px solid #333; font-weight:600;">
                                            Engine</th>
                                    </tr>
                                </thead>
                                <tbody id="idTbodyHistory">
                                    <!-- Data akan diisi di sini -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- start page Download File Form SMS -->
                <div id="mainContentDownloadFile" style="display:none; border:1px solid  
                    border-radius:10px; background:#ffffff; margin-bottom:28px; 
                    box-shadow:0 4px 10px rgba(0,0,0,0.05); overflow:visible;">

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
                                    style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">Name
                                    Vessel</label>
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
                                    <option value="">Select Category</option>
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

                <!-- end page Download File Form SMS -->

                <!-- start page Upload File Form SMS -->

                <div id="content-file-upload" style="display:none; border:1px solid  
                    border-radius:10px; background:#ffffff; margin-bottom:28px; 
                    box-shadow:0 4px 10px rgba(0,0,0,0.05); overflow:visible;">

                    <div
                        style="display:flex; justify-content:space-between; align-items:center;
                         padding:16px 20px; background:linear-gradient(90deg,#f9fafb,#f3f4f6); border-bottom:1px solid ">
                        <h4 style="margin:0; font-weight:600; color:#111827; font-size:17px;
                            display:flex; align-items:center; letter-spacing:0.3px;">
                            <i class="fa fa-upload" style="color:#2563eb; margin-right:8px;"></i> Form Upload File
                            SMS
                        </h4>
                        <button type="button" id="btn-form-cancel-upload"
                            style="background:#ef4444; border:none; padding:8px 14px; border-radius:6px;
                            color:white; font-size:14px; font-weight:500; display:flex; align-items:center; cursor:pointer;">
                            <i class="fa fa-times" style="margin-right:6px;"></i>Cancel
                        </button>
                    </div>

                    <div style="padding:20px; background:#ffffff; border-bottom:1px solid #f3f4f6;">
                        <form id="formUploadSMS" method="POST" enctype="multipart/form-data">
                            <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start;">

                                <div style="flex:1; min-width:220px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        Departement
                                    </label>
                                    <select name="slcDepartementUpload" class="slcDepartementUpload" style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db;
                                     border-radius:8px; background:#f9fafb; font-size:14px;">
                                        <option value="DECK">DECK</option>
                                        <option value="ENGINE">ENGINE</option>
                                    </select>
                                </div>

                                <div style="flex:1; min-width:220px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        Name Vessel
                                    </label>
                                    <select name="slcVesselUpload" class="slcVesselUpload" style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db;
                                     border-radius:8px; background:#f9fafb; font-size:14px;">
                                        <?php echo $vessel; ?>
                                    </select>
                                </div>

                                <div style="flex:1; min-width:200px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        Vessel Type
                                    </label>
                                    <select name="slcVesselTypeUpload" class="slcVesselTypeUpload" style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db;
                                         border-radius:8px; background:#f9fafb; font-size:14px;">
                                        <?php echo $vesselType; ?>
                                    </select>
                                </div>

                                <div style="flex:1; min-width:200px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        Category
                                    </label>
                                    <select name="txtCategoryUpload" class="txtCategoryUpload" style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db;
                                    border-radius:8px; background:#f9fafb; font-size:14px;">
                                        <option value="">Select Category</option>
                                    </select>
                                </div>

                                <div style="flex:1; min-width:200px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        File Name
                                    </label>
                                    <select name="slcFileNameUpload" class="slcFileNameUpload" style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db;
                                         border-radius:8px; background:#f9fafb; font-size:14px;">
                                        <option value="">Select File Name</option>
                                    </select>

                                </div>
                                <input type="hidden" name="action" id="action" value="upload-file-update">
                                <div style="flex:1; min-width:220px;">
                                    <label
                                        style="font-size:14px; color:#374151; font-weight:600; display:block; margin-bottom:6px;">
                                        Upload File<span style="color:red;margin-left:3px;">*</span>
                                    </label>
                                    <input type="file" name="fileUpload" id="fileUpload"
                                        accept=".xls,.xlsx,.csv,.txt,.pdf"
                                        style="width:100%; height:42px; padding:10px; border:1px solid #d1d5db; border-radius:8px; background:#f9fafb; font-size:14px;"
                                        required>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style="padding:20px; display:flex; gap:20px; justify-content:flex-end; background:#ffffff;">
                        <button type="button" onclick="resetFilters()" style="background:#6b7280; border:none; height:42px; padding:0 24px;
                          border-radius:8px; color:white; font-size:14px; font-weight:500; cursor:pointer;">
                            <i class="fa fa-undo" style="margin-right:6px;"></i> Reset Filters
                        </button>

                        <button type="submit" form="formUploadSMS" style="background:#2563eb; border:none; height:42px; padding:0 24px;
                             border-radius:8px; color:white; font-size:14px; font-weight:500; cursor:pointer;">
                            <i class="fa fa-paper-plane" style="margin-right:6px;"></i> Submit
                        </button>
                    </div>
                </div>
                <!-- end page Upload File Form SMS -->

            </section>
        </main>
    </section>
</body>

</html>