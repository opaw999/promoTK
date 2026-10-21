<style>
    #dtr_process .modal-content {
        background-image: url('<?= base_url('assets/dist/img/exportbg.jpg') ?>');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        color: white;
        border: none;
        border-radius: 0;
        height: 100%;
    }


    .list-group-item {
        color: black;
        text-decoration: none;
        display: block;
        padding: 10px;
    }

    .list-group-item:hover {
        background-color: #007bff;
        color: white;
    }

    .list-group {
        position: absolute;
        z-index: 999;
        max-height: 200px;
        width: 100%;
        overflow-y: auto;
    }

    .dropdown-list {
        position: relative;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Early Cutoff</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">DTR</li>
                        <li class="breadcrumb-item active">Early Cutoff</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <form action="<?= base_url('printEarlyCO') ?>" method="get" target="_blank">
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Employee Selection </h3>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searching">Search Employee</label>
                                        <input type="hidden" name="empId">
                                        <input type="text" class="form-control" id="searching" name="searchPromo" onkeyup="searchEarlyCo(this.value)" placeholder="Search employee..." autocomplete="off">
                                        <div class="dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateFrom">Start Date</label>
                                        <input type="text" class="form-control datetimepicker-input" id="dateFrom" name="dateFrom" onchange="dateFromcs(this.value)" data-toggle="datetimepicker" data-target="#dateFrom" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateTo">End Date</label>
                                        <input type="text" class="form-control datetimepicker-input" id="dateTo" name="dateTo" onchange="dateTocs(this.value)" data-toggle="datetimepicker" data-target="#dateTo" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary submit" disabled>Generate</button>
                        </div>

                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Employee List </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Emplyee ID</th>
                                        <th>Name</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="promos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>