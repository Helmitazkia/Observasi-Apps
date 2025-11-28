<?php $this->load->view('front/menu');?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <script type="text/javascript">
    $(document).ready(function() {
        $(document).on("click", ".btnAddClone", function() {
            var $parent = $(this).closest(".form-group-clone");
            var $clone = $parent.clone();

            $clone.find("input, textarea, select").val("");
            $clone.find(".btnRemoveClone").show();

            $("#formCloneContainer").append($clone);
            $("#formCloneContainer .form-group-clone:first .btnRemoveClone").show();
        });

        $(document).on("click", ".btnRemoveClone", function() {
            $(this).closest(".form-group-clone").remove();

            if ($(".form-group-clone").length === 1) {
                $(".form-group-clone:first .btnRemoveClone").hide();
            }
        });

        $(document).on("click", ".btnRemoveClone", function() {
            $(this).closest(".form-group-clone").remove();
        });

        $("#btnSubmitUpload").click(function() {
            var formData = new FormData();

            formData.append("idEdit", $("#idEdit").val());

            $(".form-group-clone").each(function(i, el) {
                formData.append("slcVesselType[]", $(el).find(".slcVesselType").val());
                formData.append("txtCategory[]", $(el).find(".txtCategory").val());
                formData.append("fileName[]", $(el).find(".fileName").val());
                formData.append("txtRemark[]", $(el).find(".txtRemark").val());
                formData.append("slcVessel[]", $(el).find("select[name='slcVessel[]']").val());

                var file = $(el).find(".fileUpload")[0].files[0];
                if (file) {
                    formData.append("fileUpload[]", file);
                }
            });

            $.ajax({
                url: "<?php echo base_url('masterForm/saveFile'); ?>",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                beforeSend: function() {
                    $("#idLoadingSpinner").fadeIn(200);
                },
                success: function(res) {
                    $("#idLoadingSpinner").fadeOut(200);
                    alert(res);

                    $("#formCloneContainer").find("input[type=text], textarea").val("");
                    $("#formCloneContainer").find("select").prop("selectedIndex", 0);
                    $("#formCloneContainer").find("input[type=file]").val("");
                    $("#idEdit").val("");

                    location.reload(true);
                },
                error: function() {
                    $("#idLoadingSpinner").fadeOut(200);
                    alert("Error saving file!");
                }
            });

        });
    });

    $(document).ready(function() {

        $("#btnAdd").click(function() {
            $("#idEdit").val("");

            $("#formCloneContainer .form-group-clone:not(:first)").remove();

            var $first = $("#formCloneContainer .form-group-clone:first");
            $first.find("select.slcDepartment").prop("selectedIndex", 0);
            $first.find("input.txtCategory, input.fileName, textarea.txtRemark").val("");
            $first.find("input.fileUpload").val("");
            $first.find(".viewCurrentFile").hide().html("");
            $first.find(".btnRemoveClone").hide();

            $("#mainContentDataTable").fadeOut(300, function() {
                $("#mainContentMasterFile").slideDown(400);
            });
        });

        $("#btnCancelUpload").click(function() {
            $("#idEdit").val("");

            $("#formCloneContainer .form-group-clone:not(:first)").remove();
            var $first = $("#formCloneContainer .form-group-clone:first");
            $first.find("input[type='text'], textarea").val("");
            $first.find("select").prop("selectedIndex", 0);
            $first.find("input[type='file']").val("");
            $first.find(".viewCurrentFile").hide().html("");
            $first.find(".btnRemoveClone").hide();

            $("#mainContentMasterFile").slideUp(300, function() {
                $("#mainContentDataTable").fadeIn(400);
            });
        });

        initCategoryAutocomplete("#txtCategory", "<?php echo base_url('masterForm/getCategories'); ?>");

    });

    function initCategoryAutocomplete(selector, url) {
        $(document).on('focus', selector, function() {
            var $el = $(this);

            if ($el.data('ui-autocomplete')) return;

            $el.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 2,
                delay: 250,
                select: function(event, ui) {
                    $el.val(ui.item.label || ui.item.value);
                    return false;
                }
            });
        });
    }

    $(document).ready(function() {
        getData("");

        function getData(txtSearch) {
            $.ajax({
                url: "<?php echo base_url('masterForm/getData/search'); ?>",
                type: "POST",
                data: {
                    txtSearch: txtSearch
                },
                dataType: "json",
                beforeSend: function() {
                    $("#idTbodyMainContentTable").html(
                        "<tr><td colspan='7' style='text-align:center;'>Loading...</td></tr>"
                    );
                },
                success: function(res) {
                    if (res.trNya && res.trNya !== "") {
                        $("#idTbodyMainContentTable").html(res.trNya);
                    } else {
                        $("#idTbodyMainContentTable").html(
                            "<tr><td colspan='7' style='text-align:center;'>No data found</td></tr>"
                        );
                    }
                },
                error: function() {
                    $("#idTbodyMainContentTable").html(
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

    function editFile(id) {
        $.ajax({
            url: "<?php echo base_url('masterForm/getFileById/'); ?>/" + id,
            type: "GET",
            dataType: "json",
            beforeSend: function() {
                $("#idLoadingSpinner").fadeIn(200);
                $(".btnAddClone").hide();
            },
            success: function(res) {
                $("#idLoadingSpinner").fadeOut(200);

                if (res.length > 0) {
                    const item = res[0];
                    $(".slcVessel").val(item.vessel);
                    $(".slcDepartment").val(item.department);
                    $(".txtCategory").val(item.category);
                    $(".fileName").val(item.filename);
                    $(".txtRemark").val(item.remarks);
                    $(".fileUpload").val("");
                    if (item.uploadFile && item.uploadFile !== "") {

                        const fileUrl = "<?php echo base_url(); ?>" + item.uploadFile;
                        $(".viewCurrentFile")
                            .html(
                                `<small style="display:block;margin-top:4px;">
                                📂 Current File: 
                                <a href="${fileUrl}" target="_blank" style="color:#2563eb;font-weight:600;">
                                    View
                                </a>
                            </small>`
                            )
                            .show();
                    } else {
                        $(".viewCurrentFile").hide();
                    }

                    $("#idEdit").val(item.id);

                    $("#mainContentDataTable").fadeOut(300, function() {
                        $("#mainContentMasterFile").slideDown(400);
                    });
                }
            },
            error: function() {
                $("#idLoadingSpinner").fadeOut(200);
                alert("Failed to fetch data for edit!");
            }
        });
    }
    $(document).ready(function() {
        function initCategoryAutocomplete($el) {
            $el.autocomplete({
                source: function(request, response) {
                    var $container = $el.closest("div.form-group, div[style*='flex']");
                    var department = $container.prev().find(".slcDepartment").val();
                    var vessel = $container.prev().prev().find(".slcVessel").val();

                    $.ajax({
                        url: "<?php echo base_url('masterForm/getCategories'); ?>",
                        dataType: "json",
                        data: {
                            term: request.term,
                            department: department,
                            vessel: vessel
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    loadFileData();
                }
            });
        }

        $(".txtCategory").each(function() {
            if (!$(this).hasClass("ac-initialized")) {
                initCategoryAutocomplete($(this));
                $(this).addClass("ac-initialized");
            }
        });

        $(document).on("click", ".btnAddClone", function() {
            setTimeout(function() {
                $(".txtCategory").each(function() {
                    if (!$(this).hasClass("ac-initialized")) {
                        initCategoryAutocomplete($(this));
                        $(this).addClass("ac-initialized");
                    }
                });
            }, 200);
        });

        $(document).on("change", ".slcVessel, .slcDepartment", function() {
            loadFileData();
        });
    });

    function loadFileData() {
        var vessel = $(".slcVessel").val();
        var dept = $(".slcDepartment").val();
        var cat = $(".txtCategory").val();

        if (vessel && dept && cat) {
            $.ajax({
                url: "<?php echo base_url('masterForm/getCategories'); ?>",
                type: "POST",
                data: {
                    vessel: vessel,
                    department: dept,
                    category: cat
                },
                success: function(res) {
                    $("#idTbodyMainContentTable").html(res);
                }
            });
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const tableRows = document.querySelectorAll("#mainContentDataTable table tbody tr");
        tableRows.forEach(row => {
            row.addEventListener("mouseover", () => {
                row.style.backgroundColor = "#f9fafb";
                row.style.transition = "background-color 0.2s ease-in-out";
            });
            row.addEventListener("mouseout", () => {
                row.style.backgroundColor = "transparent";
            });
        });
    });
    </script>
</head>

<body style="background:#f5f7fa; font-family:'Segoe UI', Tahoma, sans-serif;">
    <section id="container">
        <section id="main-content">
            <section class="wrapper site-min-height"
                style="min-height:400px; font-family:'Segoe UI', Tahoma, sans-serif; background:#f9fafb; padding:28px;">
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
                        <circle cx="25" cy="25" r="20" fill="none" stroke="white" stroke-width="5"
                            stroke-linecap="round" stroke-dasharray="31.4 31.4" transform="rotate(-90 25 25)">
                            <animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25"
                                dur="1s" repeatCount="indefinite" />
                        </circle>
                    </svg>

                    <p style="margin-top:20px; font-size:16px; color:#fff; font-weight:bold; text-align:center;">
                        ⏳ Please wait... Processing data
                    </p>
                </div>

                <div id="mainContentDataTable"
                    style="border:1px solid #e5e7eb; border-radius:10px; background:#fff; margin-bottom:30px; 
                     box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow:hidden; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; 
                         border-bottom:1px solid #e5e7eb; background:#f9fafb;">
                        <h4
                            style="margin:0; font-weight:600; color:#111827; font-size:17px; display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-folder-open" style="color:#2563eb;"></i> Master Form SMS
                        </h4>

                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="display:flex; border:1px solid #d1d5db; border-radius:6px; overflow:hidden;">
                                <input type="text" id="txtSearch" placeholder="Search..."
                                    style="border:none; padding:8px 10px; outline:none; font-size:13px; width:200px;">
                                <button id="btnSearch"
                                    style="background:#2563eb; color:#fff; border:none; padding:8px 14px; cursor:pointer; font-size:0.9rem;">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>

                            <button type="button" id="btnAdd" style="background:linear-gradient(90deg, #2563eb, #1d4ed8); color:#fff; border:none; 
                                border-radius:6px; padding:8px 15px; font-size:0.95rem; display:flex; align-items:center; gap:6px; 
                                cursor:pointer; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
                                <i class="fa fa-plus"></i> Add New
                            </button>
                        </div>
                    </div>



                    <div style="padding:20px;">
                        <div style="border-radius:8px; overflow:hidden; border:1px solid #e5e7eb;">
                            <table class="table table-hover align-middle mb-0"
                                style="width:100%; border-collapse:collapse; font-size:13px; ">
                                <thead>
                                    <tr style="background-color:#7192AF; color:white    ; text-align:left;">
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb; width:50px;">No
                                        </th>
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;">Vessel Name</th>
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;">Vessel Type</th>
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;">Category</th>
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;">Filename</th>
                                        <th
                                            style="padding:10px 12px; border-bottom:1px solid #e5e7eb; min-width:180px;">
                                            File</th>
                                        <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;">Remark</th>
                                        <th
                                            style="padding:10px 12px; border-bottom:1px solid #e5e7eb; width:120px; text-align:center;">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody id="idTbodyMainContentTable" style="color:#1f2937;">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="mainContentMasterFile"
                    style="display:none; border:1px solid #e5e7eb; border-radius:12px; background:#fff; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.06);">

                    <div style="padding:18px 24px; border-bottom:1px solid #e5e7eb; background:#f9fafb;">
                        <h6
                            style="margin:0; font-weight:600; color:#111827; font-size:1.15rem; display:flex; align-items:center;">
                            <i class="fa fa-upload me-2" style="color:#22c55e; font-size:1.2rem; margin-right:8px;"></i>
                            Upload Form SMS
                        </h6>
                    </div>

                    <div id="formCloneContainer" style="padding:24px; display:flex; flex-direction:column; gap:20px;">
                        <div class="form-group-clone"
                            style="display:flex; flex-wrap:wrap; gap:20px; border:1px solid #e5e7eb; padding:20px; border-radius:10px; background:#fafafa; position:relative; box-shadow:inset 0 1px 3px rgba(0,0,0,0.03); transition:0.2s;">

                            <div style="min-width:200px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Nama Kapal</label>
                                <select name="slcVessel[]" class="slcVessel form-control input-xs"
                                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; font-size:14px; color:#111827;">
                                    <?php echo $vessel; ?>
                                </select>
                            </div>

                            <div style="min-width:200px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Vessel Type</label>
                                <select name="slcVesselType[]" class="slcVesselType"
                                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                                    <?php echo $vesselType; ?>
                                </select>
                            </div>

                            <div style="min-width:200px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Category</label>
                                <input type="text" name="txtCategory[]" class="txtCategory"
                                    placeholder="Type to search..."
                                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                            </div>

                            <div style="min-width:200px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">File Name</label>
                                <input type="text" name="fileName[]" class="fileName" placeholder="Enter file name"
                                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                            </div>

                            <div style="min-width:220px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Upload File</label>
                                <div style="position:relative;">
                                    <input type="file" name="fileUpload[]" class="fileUpload"
                                        style="width:100%; padding:10px; border:1px dashed #cbd5e1; border-radius:10px; background:#f9fafb; font-size:14px; cursor:pointer;"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    <div style="font-size:12px; color:#6b7280; margin-top:6px;">Allowed: PDF, Word,
                                        Excel</div>
                                </div>
                                <div class="viewCurrentFile" style="margin-top:8px; display:none;"></div>
                            </div>

                            <div style="min-width:240px; flex:1;">
                                <label style="font-weight:600; color:#374151; font-size:14px;">Remark</label>
                                <textarea name="txtRemark[]" class="txtRemark" rows="2" placeholder="Enter remarks..."
                                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; resize:none;"></textarea>
                            </div>

                            <div
                                style="display:flex; flex-direction:column; justify-content:flex-end; gap:8px; min-width:50px;">
                                <button type="button" class="btnAddClone"
                                    style="background:#22c55e; color:#fff; border:none; padding:8px 12px; border-radius:8px; font-size:14px; cursor:pointer; transition:0.2s;">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btnRemoveClone"
                                    style="background:#ef4444; color:#fff; border:none; padding:8px 12px; border-radius:8px; font-size:14px; cursor:pointer; display:none; transition:0.2s;">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <input type="hidden" id="idEdit" name="idEdit" value="">
                    <div style="padding:20px; border-top:1px solid #e5e7eb; background:#f9fafb; text-align:right;">
                        <button type="button" id="btnCancelUpload"
                            style="border:1px solid #d1d5db; background:#fff; padding:10px 20px; border-radius:8px; margin-right:8px; font-weight:500; color:#374151; transition:0.2s;">
                            <i class="fa fa-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" id="btnSubmitUpload"
                            style="background:#2563eb; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:500; box-shadow:0 2px 6px rgba(37,99,235,0.3); transition:0.2s;">
                            <i class="fa fa-save me-1"></i> Submit
                        </button>
                    </div>
                </div>



            </section>

        </section>
    </section>

</body>

</html>