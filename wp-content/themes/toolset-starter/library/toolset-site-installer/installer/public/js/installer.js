;(function( $ ) {
    var currentSlide = 0,
        viewport = $( '.viewport' ),
        slideContainer = $( '.slide-container' ),
        slide = $( '.slide' ),
        slideCount = slide.length,
        animationTime = 300,
        btnLoadingClass = 'button--loading',
        btnNext = $( '.next' ),
        btnPrev = $( '.previous' ),
        btnExit = $( '.exit' ),
        pagination = $( '.pagination' ),
        dialogConfirm = $( '#dialog-confirm' ).dialog( {
            autoOpen: false,
            modal: true,
            width: 500
        } );

    var previousIndicator = false,
        slideCount = $( '.pagination > .indicator' ).length;
    $( '.pagination > .indicator' ).each( function( index ) {

        // if current step is already complete
        if( $( this ).hasClass( 'complete' ) && ( currentSlide + 1 ) < slideCount ) {
            currentSlide = index + 1;

            // if not complete, and previous step is complete abort loop (we cannot overstep an unfinished step)
        } else if( previousIndicator && previousIndicator.hasClass( 'complete' ) ) {
            return false;
        }

        previousIndicator = $( this );
    } );

    function setSlideDimensions() {
        var windowWidth = viewport.width();
        slideContainer.width( windowWidth * slideCount );
        slide.width( windowWidth );
    }

    function goToNextSlide() {
        if( currentSlide >= slideCount - 1 ) {
            btnNext.removeClass( btnLoadingClass );
            return;
        }
        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'step_requirements_done',
                tt_step_id: currentSlide
            },
            success: function( data ) {
                if( data != 1 ) {
                    var msg = $( '.tt-step' ).eq( currentSlide ).data( 'msg-missing-requirements' ),
                        blockNext = $( '.tt-step' ).eq( currentSlide ).data( 'missing-requirements-block' )
                            ? $( '.tt-step' ).eq( currentSlide ).data( 'missing-requirements-block' )
                            : 0;
                    msg = msg ? msg : toolsetinstaller.l10n.requirements_not_done;
                    dialogConfirm.html( msg );

                    var dialogButtons = blockNext == 0
                        ? {
                        Cancel: function() {
                            $( this ).dialog( 'close' );
                            refreshControlButtons();
                            return;
                        },
                        "Confirm": function() {
                            $( this ).dialog( 'close' );
                            nextStep();
                        }
                    } : [{
                        text: toolsetinstaller.l10n.exit_installer,
                        click: function() {
                                $( this ).dialog( 'close' );
                                refreshControlButtons();
                                return;
                            }
                        }, {
                        text:  blockNext,
                        click: function() {
                            $( this ).dialog( 'close' );
                            refreshControlButtons();
                            return;
                        }
                    }];

                    dialogConfirm.dialog( {
                        buttons: dialogButtons
                    } );
                    dialogConfirm.dialog( 'open' );
                } else {
                    nextStep();
                }
            }
        } );
    }

    function nextStep( onSuccess ) {
        currentSlide++;
        loadStep( currentSlide, slideToNextStep );
    }

    function slideToNextStep() {
        var windowWidth = viewport.width();

        slideContainer.animate( {
            left: -(windowWidth * currentSlide)
        } );
        setActiveIndicator();
        $( '.progress-bar' ).eq( currentSlide - 1 ).animate( {
            width: '100%'
        }, animationTime, function() {
            refreshControlButtons();
        } );
    }

    function goToPreviousSlide() {
        if( currentSlide <= 0 ) {
            return;
        }
        var windowWidth = viewport.width();
        currentSlide--;
        slideContainer.animate( {
            left: -(windowWidth * currentSlide)
        }, animationTime );
        setActiveIndicator();
        $( '.progress-bar' ).eq( currentSlide ).animate( {
            width: '0%'
        }, animationTime, function() {
            refreshControlButtons();
        } );
    }

    function refreshControlButtons() {
        btnNext.removeClass( btnLoadingClass );

        if( currentSlide <= 0 ) {
            btnNext.show();
            btnPrev.hide();
            btnExit.hide();
        } else if( currentSlide >= slideCount - 1 ) {
            btnNext.hide();
            btnPrev.show();
            btnExit.show();
        } else {
            btnNext.show();
            btnPrev.show();
            btnExit.hide();
        }
    }

    function positionSlides() {
        var windowWidth = viewport.width();
        setSlideDimensions();
        slideContainer.css( {
            left: -(windowWidth * currentSlide)
        }, animationTime );
    }

    function setActiveIndicator() {
        var $indicator = $( '.indicator' );
        $indicator.removeClass( 'active' ).removeClass( 'complete' );
        $indicator.eq( currentSlide ).addClass( 'active' );
        for( var i = 0; i < currentSlide; i++ ) {
            $indicator.eq( i ).addClass( 'complete' );
        }
    }

    function loadStep( stepId, onSuccess ) {
        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'load_step',
                tt_step_id: stepId
            },
            dataType: 'json',
            success: function( data ) {
                $( '.slide-container' ).find( '.slide' ).eq( currentSlide ).html( data.html );
                $( document ).trigger( 'loadStepFinished' );

                if( typeof onSuccess === 'function' ) {
                    onSuccess();
                }

                //if( data.finished == 1 )
                //    goToNextSlide();

                slideContainer.show();
            }
        } );
    }


    setSlideDimensions();
    //generatePagination();
    pagination.find( '.indicator' ).eq( currentSlide ).addClass( 'active' );
    slideToNextStep();
    loadStep( currentSlide );
    $( window ).resize( positionSlides );

    var btnNextClickCallback = function() {
        if( $( this ).hasClass( btnLoadingClass ) ) {
            return;
        }

        $( this ).addClass( btnLoadingClass );
        switch( $( '.indicator.active' ).data( 'step' ) ) {
            case 'plugin-installation':
                step_plugin_installation();
                break;
            case 'demo-import':
                step_import_settings_demo();
                break;
            case 'site-key':
                step_site_key();
                break;
            default:
                goToNextSlide();
                break;
        }
    }
    btnNext.on( 'click.nextStep', btnNextClickCallback );

    btnPrev.on( 'click', goToPreviousSlide );

    btnExit.on( 'click', function() {
        $( window ).off( 'beforeunload' );
        location.href = $( this ).data( 'url' );
    } );

    $( window ).on( 'beforeunload', function() {
        return 'Installer not finished, do you really want to quit?';
    } );

    /**
     * step actions
     */
    // site key
    function step_site_key() {
        var inputSiteKey = $( '#site_key' );

        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'site_key',
                tt_step_id: currentSlide,
                site_key: inputSiteKey.val()
            },
            success: function( data ) {
                if( data === 'success' ) {
                    goToNextSlide();
                } else {
                    $( '#dialog-confirm-content' ).html( '<p>'+data+'</p>' );
                    dialogConfirm.dialog( {
                        buttons: [{
                            text: toolsetinstaller.l10n.close,
                            "class": "tt-btn-action",
                            click: function() {
                                $( this ).dialog( 'close' );
                                refreshControlButtons();
                                return;
                            }
                        }]
                    } );
                    dialogConfirm.dialog( 'open' );
                }
            }
        } );
    }

    // plugin installation
    var plugins;

    function step_plugin_installation() {
        plugins = $( '#plugins' ).find( ':checkbox:checked' ).map( function( i, e ) {
            if( $( this ).data( 'active' ) != 1 ) {
                return e;
            }
        } ).get();

        // prove if theme is allowed on this host
        if( plugins.length > 0 ) {
            $.ajax( {
                type: 'POST',
                url: toolsetinstaller.ajax_url,
                data: {
                    request_key: toolsetinstaller.request_key,
                    action: 'tt_ajax',
                    command: 'host_allowed',
                    tt_step_id: currentSlide
                },
                success: function( data ) {
                    if( data === 'success' ) {
                        run_plugin_installation();
                        return;
                    } else if( data === 'error' ) {
                        var template = _.template( $( "#dialog-host-not-allowed" ).html() );
                        $( '#dialog-confirm-content' ).html( template() );
                        dialogConfirm.dialog( {
                            buttons: [{
                                text: toolsetinstaller.l10n.close,
                                "class": "tt-btn-action",
                                click: function() {
                                    $( this ).dialog( 'close' );
                                    refreshControlButtons();
                                    return;
                                }
                            }]
                        } );
                        dialogConfirm.dialog( 'open' );
                    } else {
                        var template = _.template( $( "#dialog-cannot-connect-to-server" ).html() );
                        $( '#dialog-confirm-content' ).html( template( { error_msg: data }) );
                        dialogConfirm.dialog( {
                            buttons: [{
                                text: toolsetinstaller.l10n.cancel_setup,
                                "class": "tt-btn-action",
                                click: function() {
                                    window.location =  $( '#tt-exit-installer' ).attr( 'href' );
                                    return;
                                }
                            }]
                        } );
                        dialogConfirm.dialog( 'open' );
                    }
                }
            } );
        } else {
            goToNextSlide();
        }

    }

    // import settings and demo
    var viewModifiedItemsDecisionPerItem = Backbone.View.extend( {
        initialize: function( options ) {
            _.extend( this, _.pick( options, "items_groups", "plugin", "allowed_operations" ) );
            this.render();
        },
        render: function() {
            var template = _.template( $( "#dialog-modified-items" ).html() );
            var result = template( {
                items_groups: this.items_groups,
                plugin: this.plugin,
                allowed_operations: this.allowed_operations
            } );

            this.$el.html( result );
        }
    } );

    var viewModifiedItemsMasterDecision = Backbone.View.extend( {
        initialize: function( options ) {
            _.extend( this, _.pick( options ) );
            this.render();
        },
        render: function() {
            var template = _.template( $( "#dialog-modified-items-master-decision" ).html() );

            this.$el.html( template() );
        }
    } );

    var import_done = false,
        demoImportProcessStatusInterval;

    function updateProcessStatus() {
        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'import-process'
            },
            dataType: 'json',
            success: function( data ) {
                if( data.response != toolsetinstaller.key_no_import_process ) {
                    updateProcessBar( data.response );
                }
            }
        } );
    }

    function updateProcessBar( number ) {
        $( '#tt-process-status-percentage' ).html( number + '%' );
    }

    function step_import_settings_demo( user_decisions_modified_items, master_decision ) {
        if( typeof user_decisions_modified_items === 'undefined' ) {
            user_decisions_modified_items = 0;
        }

        if( typeof master_decision === 'undefined' ) {
            master_decision = 0;
        }

        if( import_done === true ) {
            goToNextSlide();
        }

        var demoImportProcess = $( '#tt-demo-process-status' );

        if( ! import_done ) {
            demoImportProcess.find( '.tt-process-status-loading' ).show();
            demoImportProcess.find( '.tt-process-status-aborted-by-user' ).hide();
            demoImportProcess.show();
            if( ! demoImportProcessStatusInterval ) {
                demoImportProcessStatusInterval = setInterval( updateProcessStatus, 1000 );
            }
        }

        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'import',
                // Not optional to import plugins
                // tt_user_choice_import_plugins: $( "#import-plugins" ).is( ":checked" ) ? 1 : 0,
                tt_user_choice_import_wordpress: $( "#import-wordpress" ).is( ":checked" ) ? 1 : 0,
                tt_modified_items: user_decisions_modified_items,
                tt_master_decision: master_decision
            },
            dataType: 'json',
            success: function( response ) {
                if( response.success === true || response.success === false ) {
                    demoImportProcess.find( '.tt-process-status-loading' ).hide();
                    btnNext.removeClass( btnLoadingClass );

                    if( response.success === true ) {
                        clearInterval( demoImportProcessStatusInterval );
                        import_done = true;
                        demoImportProcess.find( '.tt-process-status-finished-success' ).show();
                    } else {
                        demoImportProcess.find( '.tt-process-status-finished-failed' ).show();
                    }
                } else {
                    demoImportProcess.find( '.tt-process-status-messages' )
                        .html(
                            demoImportProcess.find( '.tt-process-status-messages' ).html()
                            + response.data.message + '<br />'
                        );

                    if( response.data.modified_items_master_decision ) {
                        // ask in general what to do with modified items
                        new viewModifiedItemsMasterDecision( {
                            el: $( '#dialog-confirm-content' )
                        } );

                        dialogConfirm.dialog( {
                            buttons: [{
                                text: toolsetinstaller.l10n.continue,
                                "class": "tt-btn-action",
                                click: function() {
                                    $( this ).dialog( 'close' );
                                    step_import_settings_demo( 0, $( 'input[name=tt_modified_items_master_decision]:checked' ).val() );
                                    return;
                                }
                            }]
                        } );
                        dialogConfirm.dialog( 'open' );
                        dialogConfirm.dialog( { dialogClass: 'tt-dialog-no-close-icon' } );

                    } else if( response.data.modified_items ) {
                        // modified items requires user decision
                        new viewModifiedItemsDecisionPerItem( {
                            el: $( '#dialog-confirm-content' ),
                            items_groups: response.modified_items,
                            plugin: response.plugin,
                            allowed_operations: response.allowed_operations
                        } );

                        dialogConfirm.dialog( {
                            buttons: [{
                                text: toolsetinstaller.l10n.continue,
                                "class": "tt-btn-action",
                                click: function() {
                                    var items = $( '.js-tt-modified-items :input' ).serializeArray();
                                    $( this ).dialog( 'close' );
                                    step_import_settings_demo( items, toolsetinstaller.user_choice_decide_per_item );
                                    return;
                                }
                            }],
                            open: function() {
                                jQuery( 'a:first' ).blur();
                            }
                        } );
                        dialogConfirm.dialog( 'open' );
                        dialogConfirm.dialog( { dialogClass: 'tt-dialog-no-close-icon' } );
                    } else {
                        step_import_settings_demo();
                        return;
                    }
                }
            }
        } );
    }


    /**
     * run plugin installation
     * looped by install_plugin()
     */
    function run_plugin_installation() {
        if( plugins.length > 0 ) {
            install_plugin( plugins.splice( 0, 1 ) );
        } else {
            refreshControlButtons();
        }
    }

    /**
     * install single plugin
     *
     * @param plugin
     * @param onDone
     */
    function install_plugin( plugin, onDone ) {
        var plugin_id = $( plugin ).val(),
            plugin_status = $( '#' + plugin_id + '-status' ).find( '.status' ),
            plugin_status_progress = $( '#' + plugin_id + '-status' ).find( '.progressbar' );

        plugin_status.html( '' );
        plugin_status_progress.show();

        $.ajax( {
            type: 'POST',
            url: toolsetinstaller.ajax_url,
            data: {
                request_key: toolsetinstaller.request_key,
                action: 'tt_ajax',
                command: 'install_plugin',
                plugin: plugin_id
            },
            success: function( data ) {
                if( typeof onDone === 'function' ) {
                    onDone();
                }

                var respond = ( data != '' )
                    ? '<span style="color:#f00;font-weight:bold;">' + data + '</span>'
                    : 'active';

                if( data == '' ) {
                    $( plugin ).data( 'active', 1 );
                }

                var cred = $( '#cred' ).data( 'active' ),
                    views = $( '#views' ).data( 'active' ),
                    woo = $( '#woocommerce' ).data( 'active' ),
                    wooCred = $( '#cred-commerce' ).data( 'active' ),
                    wooViews = $( '#woocommerce-views' ).data( 'active' );

                /* Autoload extensions plugins (e.g. WooCommerce Views) */
                if( plugin_id == 'woocommerce' ) {
                    if( wooCred == 0 && cred == 1 && wooViews == 0 && views == 1 ) {
                        install_plugin( '#woocommerce-views', function() {
                            install_plugin( '#cred-commerce', function() {
                                plugin_status_progress.hide();
                                plugin_status.html( respond );
                                run_plugin_installation();
                            } );
                        } );
                    } else if( wooCred == 0 && cred == 1 ) {
                        install_plugin( '#cred-commerce', function() {
                            plugin_status_progress.hide();
                            plugin_status.html( respond );
                            run_plugin_installation();
                        } );
                    } else if( wooViews == 0 && views == 1 ) {
                        install_plugin( '#woocommerce-views', function() {
                            plugin_status_progress.hide();
                            plugin_status.html( respond );
                            run_plugin_installation();
                        } );
                    }
                } else if( plugin_id == 'views' && wooViews == 0 && woo == 1 ) {
                    install_plugin( '#woocommerce-views', function() {
                        plugin_status_progress.hide();
                        plugin_status.html( respond );
                        run_plugin_installation();
                    } );
                } else if( plugin_id == 'cred' && wooCred == 0 && woo == 1 ) {
                    install_plugin( '#cred-commerce', function() {
                        plugin_status_progress.hide();
                        plugin_status.html( respond );
                        run_plugin_installation();
                    } );
                } else if( $( plugin ).data( 'extension' ) == 0 ) {
                    plugin_status_progress.hide();
                    plugin_status.html( respond );
                    run_plugin_installation();
                }
            }
        } );
    }


    /**
     * tooltip
     * http://www.mkyong.com/jquery/how-to-create-a-tooltips-with-jquery/
     */
    var changeTooltipPosition = function( event ) {
        var tooltipX = event.pageX - 8;
        var tooltipY = event.pageY + 8;
        $( 'div.tt-tooltip' ).css( { top: tooltipY, left: tooltipX } );
    };

    var showTooltip = function( event ) {
        $( 'div.tt-tooltip' ).remove();
        $( '<div class="tt-tooltip">' + $( this ).data( 'tooltip' ) + '</div>' ).appendTo( 'body' );
        changeTooltipPosition( event );
    };

    var hideTooltip = function() {
        $( 'div.tt-tooltip' ).remove();
    };

    $( document ).on( 'mousemove', '[data-tooltip]', changeTooltipPosition );
    $( document ).on( 'mouseenter', '[data-tooltip]', showTooltip );
    $( document ).on( 'mouseleave', '[data-tooltip]', hideTooltip );

    $( document ).on( 'click', '[data-input-control-check]', function() {
        var selector = $( this ).data( 'input-control-check' );
        $( selector ).prop( 'checked', true );
    } );
    /**
     * little helper to for checking all radios in a table column
     * usage example <td data-check-column="table-selector">
     */
    $( document ).on( 'click', '[data-check-column]', function() {
        var nthChildOfColumn = $( this ).index() + 1;
        $( this ).closest( 'table' ).find( 'tbody tr td:nth-child( ' + nthChildOfColumn + ') input[type=radio]' ).prop( 'checked', true );
    } );

    /* Input Text Style
     * A pen by Alexander Erlandsson
     * http://codepen.io/alexerlandsson/pen/qEpavd
     */
    //Load animation if fields containing data on page load
    $( document ).on( 'loadStepFinished load', function() {
        $( '.tt-input-text' ).each( function() {
            if( $( this ).val() != '' ) {
                $( this ).addClass( 'tt-input-text-animation' );
            }
        } );
    } );

    //Add animation when input is focused
    $( 'body' ).on( 'focus', '.tt-input-text', function() {
        $( this ).addClass( 'tt-input-text-animation tt-input-text-animation-color' );
    } );

    //Remove animation(s) when input is no longer focused
    $( 'body' ).on( 'focusout', '.tt-input-text', function() {
        if( $( this ).val() === "" ) {
            $( this ).removeClass( 'tt-input-text-animation' );
            $( this ).removeClass( 'tt-input-text-animation-color' );
        }
    } );

    /**
     * Disable "Next" btn if input is required
     */
    $( document ).on( 'loadStepFinished load', function() {
        $( '.tt-input-required' ).each( function() {
            if( $( this ).val() === "" ) {
                btnNext.off( 'click.nextStep' ).addClass( 'tt-disabled' );
            } else {
                btnNext.on( 'click.nextStep', btnNextClickCallback ).removeClass( 'tt-disabled' );
            }
        } );
    } );

    $( 'body' ).on( 'keyup', '.tt-input-required', function() {
        if( $( this ).val() === "" ) {
            btnNext.off( 'click.nextStep' ).addClass( 'tt-disabled' );
        } else {
            btnNext.on( 'click.nextStep', btnNextClickCallback ).removeClass( 'tt-disabled' );
        }
    } );

    // exit installer
   $( '#tt-exit-installer' ).on( 'click', function( e ) {
       e.preventDefault();
       var urlWpAdmin = $( this ).attr( 'href' );

       $( '#dialog-confirm-content' ).html( '<p>'+toolsetinstaller.l10n.msg_exit_installer+'</p>' );
       dialogConfirm.dialog( {
           buttons: [
           {
               text: toolsetinstaller.l10n.abort,
               "class": "tt-btn",
               click: function() {
                   $( window ).off( 'beforeunload' );
                   window.location = urlWpAdmin;
                   return;
               }
           },
           {
               text: toolsetinstaller.l10n.return_to_setup,
               "class": "tt-btn-action",
               click: function() {
                   $( this ).dialog( 'close' );
                   return;
               }
           }]
       } );
       dialogConfirm.dialog( 'open' );
   })



})( jQuery );
