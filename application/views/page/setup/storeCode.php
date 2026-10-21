<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Promo Store Code</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Setup</li>
                        <li class="breadcrumb-item active">Promo Store Code</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Add Store Code</h3>
                    </div>
                    <form id="storeCode_form" novalidate="novalidate" autocomplete="off">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store_name">Store Name</label>
                                        <select class="form-control" id="store_name" name="store_name" onchange="getField(this,'storeCode_form')" required>
                                            <option value="">---</option>
                                            <?php
                                            $bus = $this->promo_model->locate_promo_bu($this->hrCode);
                                            foreach ($bus as $key => $row) {
                                                $val = 'data-field="' . $row['bunit_field'] . '" data-bc="' . $row['bc'] . '" ';
                                                $val .= 'data-cc="' . $row['cc'] . '" data-bu="' . $row['bu'] . '"';

                                                echo '<option value="' . $row['bunit_name'] . '" ' . $val . '>' . $row['bunit_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bunit_field">Field</label>
                                        <input type="text" class="form-control" id="bunit_field" name="bunit_field" required readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="baseId">Base ID</label>
                                        <input type="text" class="form-control" id="baseId" name="baseId" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="terminal">Terminal</label>
                                        <select class="form-control" id="terminal" name="terminal" required>
                                            <option value="">---</option>
                                            <option value="Logbox">Logbox</option>
                                            <option value="Biometric">Biometric</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cc">CC</label>
                                        <input type="text" class="form-control" id="cc" name="cc">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bc">BC</label>
                                        <input type="text" class="form-control" id="bc" name="bc">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="storeCode">Store Code</label>
                                        <input type="text" class="form-control" id="storeCode" name="storeCode">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Store Code List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="store_code_list">
                            <thead>
                                <tr>
                                    <th>Base ID</th>
                                    <th>Store Name</th>
                                    <th>Store Field</th>
                                    <th>Terminal</th>
                                    <th>CC</th>
                                    <th>BC</th>
                                    <th>Store Code</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="update_storeCode">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Promo Store Code</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <form id="update_storeCode_form" novalidate="novalidate" autocomplete="off">
                <div class="modal-body update_storeCode"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>