<?php /* Template Name: No Style Page */ ?>

<!doctype html>
<html lang="en">
    <head>
        <title>Mon site | Akobo</title>
        <link rel="icon" type="image/png" href="https://akoboblog.wpcomstaging.com/wp-content/uploads/2025/02/favicon.png">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!--<link href="style.css" rel="stylesheet">-->
        <style>
            .carousel-inner > .carousel-item > img {
                width: 640px;
                height: 360px;
                object-fit: contain;
            }

            .carousel-inner {
                background-color: black;
            }

            .modal-footer {
                display: flex;
                justify-content: center;
            }

            /* The slider itself */
            .slider {
                -webkit-appearance: none;  /* Override default CSS styles */
                appearance: none;
                width: 90%;
                position: relative;
            }
            
            /* The slider handle (use -webkit- (Chrome, Opera, Safari, Edge) and -moz- (Firefox) to override default look) */
            .slider::-webkit-slider-thumb {
                -webkit-appearance: none; /* Override default look */
                appearance: none;
                transform: scale(1.75);
                cursor: pointer;
            }
            
            .slider::-moz-range-thumb {
                box-sizing: border-box;
                transform: scale(1.75);
                cursor: pointer;
            }

            .delete_tooltip {
                position: absolute;
                left: 0;
                right: 0;
                top: 15px;
                margin-inline: auto;
                width: fit-content;
            }

            .btn-file {
                position: relative;
                overflow: hidden;
            }

            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;   
                cursor: inherit;
                display: block;
            }

            #loading_image_icon {
                width: 40px;
                height: 40px;
                position: absolute;
                left: 0;
                right: 0;
                top: 160px;
                margin-inline: auto;
                visibility: hidden;
            }

            .alert-success {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                margin-inline: auto;
                width: fit-content;
                -webkit-user-select: none; /* Safari */
                -ms-user-select: none; /* IE 10 and IE 11 */
                user-select: none; /* Standard syntax */
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <?php the_content(); ?>
        <script>
            let image_field_id = 0; //each image field of the form has a unique id used as a selector for the array "selected_images"
            let selected_images = [1]; //array of images that are currently selected for each image field
            const max_images = 5;
            let images_list = params.bibliotheque;
            let carousel;
            let nb_slides_spans;
            let delete_buttons;
            let select_buttons;
            let upload_span;
            let tooltipTriggerList;
            let tooltipList;
            let upload_tooltip;
            let range_elt;

            document.addEventListener("DOMContentLoaded", (event) => {
                nb_slides_spans = document.getElementsByClassName("nb_slides");
                delete_buttons = document.getElementsByClassName("delete_btn");
                select_buttons = document.getElementsByClassName("select_btn");
                upload_span = document.getElementById("upload_span");

                //initialize delete tooltips
                tooltipTriggerList = document.getElementsByClassName("delete_tooltip");
                tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

                //initialize upload tooltip and prevent upload if max images limit reached
                document.getElementById("upload_tooltip").setAttribute("data-bs-title", max_images.toString()+" images max. Supprimez-en pour en recharger.");
                upload_tooltip = new bootstrap.Tooltip(document.getElementById("upload_tooltip"));
                upload_tooltip.disable();
                if (images_list.length == max_images) {
                    upload_tooltip.enable();
                    upload_span.classList.add("disabled");
                }
                
                update_nb_slides_spans();
                update_img_on_slides();
                update_selected_images(max_images);

                //hide unused slides
                for (let i = images_list.length; i < delete_buttons.length; i++) {
                    document.getElementById("item_"+i.toString()).classList.remove("carousel-item");
                    document.getElementById("item_"+i.toString()).classList.add("d-none");
                }

                carousel = document.getElementById("myCarousel");
                new bootstrap.Carousel(carousel);
                
                document.getElementById("upload_image").addEventListener("change", handle_file, false);

                //indicate selected image
                document.getElementById("select_btn_"+selected_images[image_field_id].toString()).classList.add("d-none");
                document.getElementById("select_alert_"+selected_images[image_field_id].toString()).classList.remove("d-none");

                //initialize range
                range_elt = document.getElementById("customRange1");
                range_elt.setAttribute("max", images_list.length-1);

                //make range listen to carousel
                carousel.addEventListener('slid.bs.carousel', function () {
                   range_elt.value = get_active_slide();
                });     

                //put carousel focus on selected image when modal shows
                document.getElementById("myModal").addEventListener('show.bs.modal', event => {
                    new bootstrap.Carousel(carousel).to(selected_images[image_field_id]);
                    range_elt.value = selected_images[image_field_id];
                });

                //save images list when modal is closed
                document.getElementById("myModal").addEventListener('hide.bs.modal', event => {
                    UploadLibrary(images_list);
                });
            });

            function moveCarouselTo(img_nb) {
                new bootstrap.Carousel(carousel).to(img_nb);
            }

            function get_active_slide() {
                for (let i = 0; i < images_list.length; i++) {
                    if (document.getElementById("item_"+i.toString()).classList.contains("active")) {
                        return i;
                    }
                }
            }

            function update_nb_slides_spans() {
                for (let i = 0; i < nb_slides_spans.length; i++) {
                    nb_slides_spans[i].innerText = images_list.length.toString();
                }
            };

            function update_img_on_slides() {
                for (let i = 0; i < images_list.length; i++) {
                    document.getElementById("img_slide_"+i.toString()).src = images_list[i];
                }
            };

            function delete_img(img_nb) {
                images_list.splice(img_nb, 1);
                update_nb_slides_spans();
                range_elt.setAttribute("max", images_list.length-1);
                update_img_on_slides();
                update_selected_images(img_nb); //update selected images whose ids need to change following image deletion

                //if last slide is currently displayed, go to previous one
                if (document.getElementById("item_"+images_list.length.toString()).classList.contains("active")) {
                    new bootstrap.Carousel(carousel).prev();
                }

                //remove last slide
                document.getElementById("img_slide_"+images_list.length.toString()).src = "";
                document.getElementById("item_"+images_list.length.toString()).classList.remove("carousel-item");
                document.getElementById("item_"+images_list.length.toString()).classList.add("d-none");

                //if no image left to display
                if (images_list.length == 0) {
                    document.getElementById("no_slide").classList.remove("d-none");
                }
                //if max images limit not reached anymore
                else if (images_list.length == max_images-1) {
                    upload_tooltip.disable();
                    upload_span.classList.remove("disabled");
                }
            };

            function add_img(img_url) {
                images_list.push(img_url);
                update_nb_slides_spans();
                range_elt.setAttribute("max", images_list.length-1);

                //if 0 to 1 image
                if (images_list.length == 1) {
                    document.getElementById("no_slide").classList.add("d-none");
                }
                //if max images reached
                else if (images_list.length == max_images) {
                    upload_tooltip.enable();
                    upload_span.classList.add("disabled");
                }

                //add slide and go to it
                document.getElementById("img_slide_"+(images_list.length-1).toString()).src = img_url;
                document.getElementById("item_"+(images_list.length-1).toString()).classList.remove("d-none");
                document.getElementById("item_"+(images_list.length-1).toString()).classList.add("carousel-item");
                new bootstrap.Carousel(carousel).to(images_list.length-1);
            };

            const countOccurrences = (arr, val) => arr.reduce((a, v) => (v === val ? a + 1 : a), 0);

            function select_img(img_nb) {
                document.getElementById("select_btn_"+selected_images[image_field_id].toString()).classList.remove("d-none");
                document.getElementById("select_alert_"+selected_images[image_field_id].toString()).classList.add("d-none");
                if (countOccurrences(selected_images, selected_images[image_field_id]) == 1) {
                    delete_buttons[selected_images[image_field_id]].disabled = false;
                    tooltipList[selected_images[image_field_id]].disable();
                }
                
                selected_images[image_field_id] = img_nb;

                document.getElementById("select_btn_"+selected_images[image_field_id].toString()).classList.add("d-none");
                document.getElementById("select_alert_"+selected_images[image_field_id].toString()).classList.remove("d-none");
                delete_buttons[selected_images[image_field_id]].disabled = true;
                tooltipList[selected_images[image_field_id]].enable();
            }

            function update_selected_images(deleted_img_nb) {
                //update ids of selected images that are after deleted image
                for (let i = 0; i < selected_images.length; i++) {
                    if (i != image_field_id) {
                        if (deleted_img_nb < selected_images[i]) {
                            selected_images[i] = selected_images[i]-1;
                        }
                    }
                }
                if (deleted_img_nb < selected_images[image_field_id]) {
                    select_img(selected_images[image_field_id]-1);
                }
                
                for (let i = 0; i < delete_buttons.length; i++) {
                    //disable delete button and enable tooltip for selected images
                    if (selected_images.includes(i)) {
                        delete_buttons[i].disabled = true;
                        tooltipList[i].enable();
                    }
                    //enable delete button and disable tooltip for other images
                    else {
                        delete_buttons[i].disabled = false;
                        tooltipList[i].disable();
                    }
                }
            }

            const UploadFile = (file) => {
                console.log('start file upload : ', file.name);
                const formData = new FormData();
                formData.append('file', file);

                const authHeader = 'Basic ' + btoa(`${username}:${applicationPassword}`);

                const response = fetch(wordpressApiUrl, {
                    method: 'POST',
                    credentials: 'omit',
                    headers: {
                        'Authorization': authHeader
                    },
                    body: formData
                });

                return response;    
            };

            const UrlFromUploadResponse = (response) => {
                if(response)
                {
                    if (response.ok)
                    {
                        return response.json()
                        .then((data)=>{return data.source_url})
                        .catch((err)=>{return ""});
                    }
                    response.json().then((err) => {console.err(err.message)});
                }
                return "";
            };

            //PENSER A AJOUTER UN CHECK AFIN DE NE PAS UPLOAD N'IMPORTE QUEL FICHIER (MEME SI CE NE SERA PAS UNE PROTECTION FIABLE COMME ON EST EN FRONT)
            async function handle_file() {

                //grey slide + show loading icon + disable buttons
                if (images_list.length > 0) {
                    document.getElementById("carousel_inner").style.webkitFilter = "brightness(50%)"; /* Safari 6.0 - 9.0 */
                    document.getElementById("carousel_inner").style.filter = "brightness(50%)";

                    document.getElementById("loading_image_icon").style.visibility = "visible";
                    
                    for (let i = 0; i < images_list.length; i++) {
                        delete_buttons[i].disabled = true;
                        select_buttons[i].disabled = true;
                    }

                    document.getElementById("carousel-control-prev").disabled = true;
                    document.getElementById("carousel-control-next").disabled = true;
                    document.getElementById("customRange1").disabled = true;
                }
                else {
                    document.getElementById("no_slide").innerHTML = "<span class='spinner-border spinner-border-sm' aria-hidden='true'></span> <span role='status'>Chargement...</span>";
                }
                upload_span.classList.add("disabled");

                //upload file
                try{
                    let res = UploadFile(this.files[0]);
                    res = await res;
                    let url = UrlFromUploadResponse(res);
                    url = await url;
                    add_img(url);
                }
                catch(err){
                    console.error('Error uploading file:', err);
                }
                
                //ungrey slide + hide loading icon + enable buttons
                if (images_list.length > 0) {
                    document.getElementById("carousel_inner").style.webkitFilter = "none"; /* Safari 6.0 - 9.0 */
                    document.getElementById("carousel_inner").style.filter = "none";
                    
                    document.getElementById("loading_image_icon").style.visibility = "hidden";

                    for (let i = 0; i < images_list.length; i++) {
                        if (!selected_images.includes(i)) {
                            delete_buttons[i].disabled = false;
                        }
                        select_buttons[i].disabled = false;
                    }

                    document.getElementById("carousel-control-prev").disabled = false;
                    document.getElementById("carousel-control-next").disabled = false;
                    document.getElementById("customRange1").disabled = false;
                }
                else {
                    document.getElementById("no_slide").innerHTML = "Aucune image à afficher.";
                }
                if (images_list.length < max_images) {
                    upload_span.classList.remove("disabled");
                }
            };

            const UploadLibrary = (library) => {
                const data = {
                        code: params.code,
                        library: JSON.stringify(library)
                    };
                return fetch(libraryWebhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
            };
        </script>
    </head>
    <body>
        
        <div class="container">
            <h1 class="main_title"><?php the_title(); ?></h1>
            <p>Click on the button to open the modal.</p>
            
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
              Open modal
            </button>
        </div>
          
        <!-- The Modal -->
        <div class="modal" id="myModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
            
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Sélection Image</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                
                    <!-- Modal body -->
                    <div class="modal-body text-center">
                        <div id="myCarousel" class="carousel slide" data-bs-interval="false">
                            <div id="carousel_inner" class="carousel-inner">
                                <div id="item_0" class="carousel-item active">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(0)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_0" src="" class="d-block w-100" alt="Image 1">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(0)" id="select_btn_0" type="button" class="btn btn-primary select_btn">Sélectionner image 1/<span class="nb_slides"></span></button>
                                        <div id="select_alert_0" class="alert alert-success d-none" role="alert">Image sélectionnée (1/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_1" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(1)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_1" src="" class="d-block w-100" alt="Image 2">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(1)" id="select_btn_1" type="button" class="btn btn-primary select_btn">Sélectionner image 2/<span class="nb_slides"></span></button>
                                        <div id="select_alert_1" class="alert alert-success d-none" role="alert">Image sélectionnée (2/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_2" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(2)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_2" src="" class="d-block w-100" alt="Image 3">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(2)" id="select_btn_2" type="button" class="btn btn-primary select_btn">Sélectionner image 3/<span class="nb_slides"></span></button>
                                        <div id="select_alert_2" class="alert alert-success d-none" role="alert">Image sélectionnée (3/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_3" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(3)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_3" src="" class="d-block w-100" alt="Image 4">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(3)" id="select_btn_3" type="button" class="btn btn-primary select_btn">Sélectionner image 4/<span class="nb_slides"></span></button>
                                        <div id="select_alert_3" class="alert alert-success d-none" role="alert">Image sélectionnée (4/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                                <div id="item_4" class="carousel-item">
                                    <span class="delete_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Suppression impossible : vous utilisez cette image sur votre site.">
                                        <button onclick="delete_img(4)" type="button" class="btn btn-danger delete_btn">Supprimer <i class="bi bi-trash"></i></button>
                                    </span>
                                    <img id="img_slide_4" src="" class="d-block w-100" alt="Image 5">
                                    <div class="carousel-caption d-block">
                                        <button onclick="select_img(4)" id="select_btn_4" type="button" class="btn btn-primary select_btn">Sélectionner image 5/<span class="nb_slides"></span></button>
                                        <div id="select_alert_4" class="alert alert-success d-none" role="alert">Image sélectionnée (5/<span class="nb_slides"></span>) <i class="bi bi-check"></i></div>
                                    </div>
                                </div>
                            </div>
                            <button id="carousel-control-prev" class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button id="carousel-control-next" class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                            <div id="loading_image_icon" class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p id="no_slide" class="d-none">Aucune image à afficher.</p>
                        <br>
                        <input type="range" class="form-range slider" min="0" max="0" id="customRange1" onchange="moveCarouselTo(this.value)">
                    </div>
                
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <span id="upload_tooltip" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title=".">
                            <span id="upload_span" class="btn btn-secondary btn-file">
                                Charger image <i class="bi bi-upload"></i><input id="upload_image" type="file" accept="image/png, image/jpeg">
                            </span>
                        </span>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>
