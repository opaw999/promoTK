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
                    <h1>Duty Table Insert</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Duty</li>
                        <li class="breadcrumb-item active">Duty Table Insert</li>
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
                        <h3 class="card-title">Duty Table Insert Form</h3>
                    </div>
                    <form id="dutyInsert" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searchPromoDuty">Search Employee</label>
                                        <input type="hidden" name="empId" required>
                                        <input type="text" class="form-control" id="searchPromoDuty" name="searchPromoDuty" onkeyup="searchPromoduty(this.value)" placeholder="Search employee..." autocomplete="off" required>
                                        <div class="dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateFrom">Date From</label>
                                        <input type="text" class="form-control datetimepicker-input datePicker" id="dateFrom" name="dateFrom" onchange="setDateto(this.value)" data-toggle="datetimepicker" data-target="#dateFrom" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateTo">Date To</label>
                                        <input type="text" class="form-control datetimepicker-input datePicker" id="dateTo" name="dateTo" data-toggle="datetimepicker" data-target="#dateTo" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
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
                                </div> -->
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