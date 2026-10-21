<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Holidays</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">View</li>
                        <li class="breadcrumb-item active">Holidays</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Holiday List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="holiday_list">
                            <thead>
                                <tr>
                                    <th>Holiday Name</th>
                                    <th>Date</th>
                                    <th>Move To</th>
                                    <th>Type</th>
                                    <th>Category</th>
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

<div class="modal fade" id="holiday">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Holiday...</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <form id="holiday_form" novalidate="novalidate">
                <div class="modal-body holiday_form"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                </div>
            </form>
        </div>
    </div>
</div>