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
                    <h1>Enable Addon</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Setup</li>
                        <li class="breadcrumb-item active">Enable Addon</li>
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
                        <h3 class="card-title">Add Previous Cutoff for Processing</h3>
                    </div>
                    <form id="addon_form" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searchPromo">Search Employee</label>
                                        <input type="hidden" name="empId">
                                        <input type="text" class="form-control" id="searchPromo" name="searchPromo" onkeyup="searchPromoaddon(this.value)" placeholder="Search employee..." autocomplete="off" required>
                                        <div class="dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateFrom">Cutoff Start</label>
                                        <select class="form-control" id="dateFrom" name="dateFrom" required>
                                            <option value="">---</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateTo">Cutoff End</label>
                                        <select class="form-control" id="dateTo" name="dateTo" required>
                                            <option value="">---</option>
                                        </select>
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
                        <h3 class="card-title">Enabled Previous Cutoff List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="perday_previous_allow" style="font-size: smaller;">
                            <thead>
                                <tr>
                                    <th>HRMS ID</th>
                                    <th>Name</th>
                                    <th>Date From</th>
                                    <th>Date To</th>
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