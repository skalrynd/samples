var printReport = {
    config: {
        baseURL: '', //base URL that points to report controller.
    },
    
    //init
    init: function(config) {
        if (typeof(config) != 'undefined') {
            printReport.config = $.extend(printReport.config, config);
        }
        printReport.setupControls();
    },
    
    getConfig: function(name) {
        return printReport.config[name];
    },
    
    //GetForm
    getForm: function() {
        return $('#report');
    },
    
    //renders the generate button in generate-btn-container.
    renderGenerateButton: function() {
        var token = $('input[name="_token"]').attr('value');
        var container = $('.generate-btn-container');
        if (container.is(':empty')) {
            var button = $('<button>')
                    .attr('type', 'button')
                    .addClass('btn btn-primary')
                    .appendTo('.generate-btn-container')
                    .html('Generate Report');
            button.on('click', function() {
                var form = printReport.getForm();
                form.validate();
                if (!form.valid()) {
                    return false;
                }
                spinButtonLoad(button);
                button.prop('disabled', true);
                $('.data-container').empty();
                var reportName = form.find('select[name=reports]').first().val();
                if (reportName.length < 1) {
                    spinButtonUnload(button);
                    button.prop('disabled', false);
                    return;
                }
                var url = printReport.getConfig('baseURL') + '/report';
                var data = form.serialize();
                $.ajax({
                    url: url,
                    data: data,
                    method: 'post',
                    dataType: 'html',
                    headers  : {
                        "X-CSRF-TOKEN" : token
                    },
                    success: function(data) {
                        $('.data-container').html(data);
                    },
                    error: function(data) {
                    	var fail_html = "";
                        if(data.status == 422)
                        {
                            var return_data = JSON.parse(data.responseText);
                            fail_html = '<i class="fa fa-exclamation-triangle"></i> <br/> ';

                            for(var key in return_data)
                            {
                              fail_html += 'Invalid Input for <b>'+key+ "</b>: ";
                              for(var array in return_data[key])
                              {
                                //console.log(return_data[key][array]);
                                  fail_html += return_data[key][array] + ','
                              }
                               fail_html += '<br/>';
                            }

                            console.log(data);
                        }
                        // permission error
                        else if(data.status == 401)
                        {
                          fail_html = '<i class="fa fa-exclamation-triangle"></i> You must be logged in to access this resource.';
                          console.log(data);
                        }
                        else if(data.status == 403)
                        {
                          fail_html = '<i class="fa fa-exclamation-triangle"></i> You do not have Permissions to access this resource.';
                          console.log(data);
                        //  console.log(xhr.responseText);
                      //    var info1 = $(data.responseText).find('.block_exception');
                        //  var info2 = $.parseHTML(xhr.responseText);
                     //     console.log(info1.html());
                      //    console.log(info2);

                       //   fail_html += "<br/><br/>"+ info1.html();
                        }
                        else if(data.status == 500)
                        {
                          fail_html = '<i class="fa fa-exclamation-triangle"></i> A 500 Internal Server Error has Occured! - Please contact the administrator.';
                          console.log(data);
                    //      var info1 = $(data.responseText).find('.block_exception');
                    //      fail_html += "<br/><br/>"+ info1.html();
                        }
                        else
                        {
                        	 fail_html = '<i class="fa fa-exclamation-triangle"></i> An unexpected error occured - Please contact the administrator.';
                             console.log(data);
                        }
                        modalAjax.modalMessage('Error',fail_html);
                    },
                    complete: function() {
                        spinButtonUnload(button);
                        button.prop('disabled', false);
                    }
                });
            });
        }
    },
    
    //Sets up controls on the page/element
    setupControls: function() {
        var token = $('input[name="_token"]').attr('value');
        var form = printReport.getForm();
        $('.params-container').hide();
        form.find('select[name=reports]').on('change', function(e) {
            spinButtonLoad('#print-report-select-container');
            $('.data-container').empty();
            $('.params-container').empty();
            if ($(e.target).val().length <= 0) {
                spinButtonUnload('#print-report-select-container');
                return;
            }
            var baseUrl = printReport.getConfig('baseURL');
            $.ajax({
                url: baseUrl + '/params',
                method: 'post', 
                data: form.serialize(),
                headers  : {
                    "X-CSRF-TOKEN" : token
                },
                success: function(data) {
                    if (data.length) {
                        $('.params-container').html(data);
                        $('.params-container').show();
                    }
                    printReport.renderGenerateButton();
                },
                complete: function() {
                    spinButtonUnload('#print-report-select-container');
                }
            });
        })
    }
};