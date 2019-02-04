@extends('app::layouts.vendor.dashboard.dashboard')

@section('js')

    <script>

        var myTimer;

        function deselectAll() {

            var element = document.activeElement;

            if (element && /INPUT|TEXTAREA/i.test(element.tagName)) {
                if ('selectionStart' in element) {
                    element.selectionEnd = element.selectionStart;
                }
                element.blur();
            }

            if (window.getSelection) { // All browsers, except IE <=8
                window.getSelection().removeAllRanges();
            } else if (document.selection) { // IE <=8
                document.selection.empty();
            }
        }

        /**
         * run when a payment input is
         * updated
         * @param self
         */
        function paymentUpdated(self) {
            updateTableRows();
            updateTotalPayments();
        }

        function updateTotalPayments() {
            $('#totalPaid').val(getTotalPayments());
        }

        function clearTicker() {
            clearTimeout(myTimer);
            myTimer = null;
        }

        /**
         * calculate dividend amount
         * @param tax
         * @param payment
         * @returns {string}
         */
        function getDividend(tax, payment) {
            var dividend = Math.max((payment-tax), 0);
            return precisionRound(dividend, 2).toFixed(2);
        }

        /**
         * add up total payments
         * @returns {number}
         */
        function getTotalPayments() {
            var t=0;
            $('.payment-amount').each(function (e) {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) {
                    t += v;
                }
            });
            return t;
        }

        /**
         * format number
         * @param number
         * @param precision
         * @returns {number}
         */
        function precisionRound(number, precision) {
            var factor = Math.pow(10, precision);
            return Math.round(number * factor) / factor;
        }

        /**
         * function called by timer
         * @param self
         */
        function paymentTimeout(self) {
            clearTicker();
            paymentUpdated(self);
        }

        function updateTableRows() {

            var rows = $('.payment-amount').length;
            var totalTax = parseFloat($('#totalTax').val());
            var i = precisionRound((totalTax / rows), 2);
            var lastOne = rows-1;
            var accumulate = 0;
            var totalPayments = getTotalPayments();
            if (isNaN(totalPayments)) {
                totalPayments = 0;
            }

            $('.payment-amount').each(function (index, ele) {

                var p = $(ele).parent().parent();
                var v = parseFloat($(ele).val());
                if (isNaN(v)) {
                    v = 0;
                }

                var percent = (v / totalPayments);
                i = precisionRound((totalTax * percent), 2);
                if (isNaN(i)) {
                    i = 0;
                }

                // console.log('i, before', i);

                if (lastOne == index) {
                    i = (totalTax - accumulate).toFixed(2);
                }

                i = Math.min(i, v);

                // console.log('i, after', i);
                // console.log('v', v);
                // console.log('dividend', getDividend(i, v));

                $('.paye-amount', $(p)).val(i);
                $('.dividend-amount', p).val(getDividend(i, v));

                accumulate += i;
            });
        }

        $( document ).ready(function() {

            $('#reset').click(function (e) {
                window.location.reload(false);
            });

            $('.copy-btn').click(function (e) {

                var p = $(e.target).closest('.input-group');
                var copyText = $('input', p)[0];

                /* copy value to clipboard */
                copyText.select();
                document.execCommand("copy");

                /*display tooltip*/
                $(".tooltip").tooltip("hide");
                $('input', p)
                    .attr('title', 'Copied to clipboard!')
                    .tooltip('show');

                /*remove selection from copied input*/
                $(e.target).focus();

                /*remove tooltip*/
                setTimeout(function () {
                    $('input', p).tooltip('dispose');
                }, 2000);
            });

            $('[data-toggle="tooltip"]').tooltip();

            $('#add_payment').click(function (e) {
                var $tr = $('tbody').children().last();
                var $cnt = parseInt($tr.find('.counter').html());
                $cnt++;
                var $clone = $tr.clone(true);
                $clone.find(':text').val(0);
                $clone.find('.counter').html($cnt);
                $clone.appendTo($('tbody'));
                updateTableRows();
                updateTotalPayments();
                $('#remove_payment').prop('disabled', false);
                $(this).tooltip('hide')
            });

            $('#remove_payment').click(function (e) {
                var rows = $('.payment-amount').length;
                var self = this;
                if (rows > 1) {
                    $('table tr:last').remove();
                    updateTableRows();
                    updateTotalPayments();
                    if (rows == 2 ) {
                        $(self).prop('disabled', true);
                    }
                }
                $(self).tooltip('hide');
            });

            $('#totalTax')
                .blur(function (e) {
                    var self = this;
                    paymentUpdated(self);
                })
            /*.bind('keyup mouseup', function () {
                var self = this;
                if(myTimer === null) {
                    myTimer = setTimeout(paymentTimeout, 1000, self);
                }
            })*/;

            $('.payment-amount')
                .blur(function (e) {
                    var self = this;
                    paymentUpdated(self);

                }).change(function (e) {
                var self = this;
                if(myTimer === null) {
                    myTimer = setTimeout(paymentTimeout, 1000, self);
                }
            });
        });

    </script>

@endsection

@section('css')

@endsection

@section('content')

    <div class="container-fluid">

        <h2 class="page-heading">Dividend Calculator</h2>

        <form method="post" name="frm" id="frm">

            {{ csrf_field() }}

            <div class="form-group">

                <label for="totalTax">Monthly Total Tax Allowance</label>
                <input type="number" type="number" step="0.01" class="form-control col-3" id="totalTax" aria-describedby="totalTax" placeholder="Total Tax" value="1000" min="0">
                <small class="form-text text-muted">
                    This is the total tax allowance for the month.
                </small>

            </div>

            <div class="form-group">

                <label for="totalPaid">Total Amount Paid</label>
                <input type="number" type="number" step="0.01" class="form-control col-3"  aria-describedby="totalPaid" placeholder="Total Paid" value="0" disabled id="totalPaid">
                <small class="form-text text-muted">Total payments made within period</small>

            </div>

            <div class="form-group mb-3">

                <button class="btn btn-outline-success mr-2 mb-1" type="button" data-toggle="tooltip" title="Add a payment line." id="add_payment">Add Payment</button>

                <button class="btn btn-outline-danger mr-2 mb-1" type="button" data-toggle="tooltip" title="Remove a new payment line." id="remove_payment" disabled>Remove Payment</button>

                <button class="btn btn-outline-primary mb-1" type="button" data-toggle="tooltip" id="reset" title="Reload the page.">Reset</button>

            </div>

            <table class="table">

                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Payment Amount</th>
                    <th scope="col">Dividend</th>
                    <th scope="col">PAYE</th>
                </tr>
                </thead>

                <tbody>
                <tr data-row="0">
                    <th scope="row" class="counter">1</th>
                    <td>
                        <input type="number" name="payment_amount[0]" step="0.01" class="payment-amount form-control mr-2" aria-describedby="" placeholder="Payment Amount">
                    </td>
                    <td>

                        <div class="input-group">

                            <input type="number" name="dividend_amount[0]" step="0.01" class="dividend-amount form-control" aria-describedby="" placeholder="Dividend Amount" value="0" readonly>
                            <div class="input-group-append">
                                <a href="javascript:void(0);" class="input-group-text copy-btn">
                                    <i class="fa fa-copy"></i>
                                </a>
                            </div>

                        </div>

                    </td>
                    <td>

                        <div class="input-group">

                            <input type="number" name="paye_amount[0]" step="0.01" class="paye-amount form-control" aria-describedby="" value="1000" placeholder="PAYE Amount" readonly>
                            <div class="input-group-append">
                                <a href="javascript:void(0);" class="input-group-text copy-btn" >
                                    <i class="fa fa-copy"></i>
                                </a>
                            </div>

                        </div>
                    </td>
                </tr>
                </tbody>

            </table>

        </form>

    </div>

@endsection
