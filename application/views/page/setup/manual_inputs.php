<style>
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
                    <h1>Manual Inputs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Setup</li>
                        <li class="breadcrumb-item active">Manual Inputs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Manual <b>LATE</b></h3>
                    </div>
                    <form id="manualLate_form" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searchPromoLate">Search Employee</label>
                                        <input type="hidden" name="lateEmpId">
                                        <input type="text" class="form-control" id="searchPromoLate" name="searchPromoLate" onkeyup="searchManualLate(this.value)" placeholder="Search employee..." autocomplete="off" required="">
                                        <div class="dropdown-list late"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_late">Date Late</label>
                                        <input type="text" class="form-control datetimepicker-input datePicker" id="date_late" name="date_late" data-toggle="datetimepicker" data-target="#date_late" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">Late Type</label>
                                        <select class="form-control" id="type" name="type" onchange="manualType(this.value)" required>
                                            <option value="">---</option>
                                            <option value="late 1">IN 1</option>
                                            <option value="late 2">IN 2</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="late1">Late [ <span class="text-danger">IN 1</span> ]</label>
                                        <input type="text" class="form-control datetimepicker-input timePicker" id="late1" name="late1" data-toggle="datetimepicker" data-target="#late1" placeholder="HH:MM" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="late2">Late [ <span class="text-danger">IN 2</span> ]</label>
                                        <input type="text" class="form-control datetimepicker-input timePicker" id="late2" name="late2" data-toggle="datetimepicker" data-target="#late2" placeholder="HH:MM" autocomplete="off" required>
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
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Manual <b>UNDERTIME</b></h3>
                    </div>
                    <form id="manualUndertime_form" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searchPromoUndertime">Search Employee</label>
                                        <input type="hidden" name="undertimeEmpId">
                                        <input type="text" class="form-control" id="searchPromoUndertime" name="searchPromoUndertime" onkeyup="searchManualUndertime(this.value)" placeholder="Search employee..." autocomplete="off" required="">
                                        <div class="dropdown-list undertime"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_undertime">Date Undertime</label>
                                        <input type="text" class="form-control datetimepicker-input datePicker" id="date_undertime" name="date_undertime" data-toggle="datetimepicker" data-target="#date_undertime" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">Undertime Type</label>
                                        <select class="form-control" id="type" name="type" onchange="manualType(this.value)" required>
                                            <option value="">---</option>
                                            <option value="undertime 1">OUT 1</option>
                                            <option value="undertime 2">OUT 2</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="undertime1">Undertime [ <span class="text-danger">OUT 1</span> ]</label>
                                        <input type="number" class="form-control" id="undertime1" name="undertime1" min="1" max="4" step="0.5" placeholder="Enter hours" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="undertime2">Undertime [ <span class="text-danger">OUT 2</span> ]</label>
                                        <input type="number" class="form-control" id="undertime2" name="undertime2" min="1" max="4" step="0.5" placeholder="Enter hours" required>
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
        </div>
    </section>
</div>