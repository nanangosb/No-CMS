<?php
    $record_index = 0;
    $upload_path = base_url('modules/'.$module_path.'/assets/uploads').'/';
?>

<style type="text/css">
    ._photo-preview{
        width : 150px;
        height : 75px;
        background-color : black;
        background-repeat : no-repeat;
        background-position:center;
    }
    .md_field_photos_col_index{
        width : 50px;
    }
    .md_field_photos_col_caption{
        width : 257px;
    }
</style>

<table id="md_table_photos" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th style="width:50px;">Index</th>
            <th style="width:150px;">Photo</th>
            <th style="width:300px;">Caption</th>
            <th style="width:100px;">Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- the data presentation be here -->
    </tbody>
</table>
<input id="md_field_photos_add" class="btn btn-default" type="button" value="Add Photo" />
<br />
<!-- This is the real input. If you want to catch the data, please json_decode this input's value -->
<input id="md_real_field_photos_col" name="md_real_field_photos_col" type="hidden" />
<?php
/*
    $asset = new Cms_asset();
    $asset->add_cms_js("nocms/js/jquery-ace/ace/ace.js");
    $asset->add_cms_js("nocms/js/jquery-ace/ace/theme-eclipse.js");
    $asset->add_cms_js("nocms/js/jquery-ace/ace/mode-html.js");
    $asset->add_cms_js("nocms/js/jquery-ace/jquery-ace.min.js");
    echo $asset->compile_js();
*/
?>
<script type="text/javascript">

    /////////////////////////////////////////////////////////////////////////
    // DATA INITIALIZATION
    //
    // * Prepare some global variables
    //
    /////////////////////////////////////////////////////////////////////////
    var DATE_FORMAT = '<?php echo $date_format ?>';
    var OPTIONS = <?php echo json_encode($options); ?>;
    var RECORD_INDEX_photos = <?php echo $record_index; ?>;
    var UPLOAD_PATH = '<?php echo $upload_path; ?>';
    var DATA_photos = {update:new Array(), insert:new Array(), delete:new Array()};
    var old_data = <?php echo json_encode($result); ?>;
    for(var i=0; i<old_data.length; i++){
        var row = old_data[i];
        var record_index = i;
        var primary_key = row['photo_id'];
        var data = row;
        delete data['photo_id'];
        DATA_photos.update.push({
            'record_index' : record_index,
            'primary_key' : primary_key,
            'data' : data,
        });
    }
    //console.log(DATA_photos);


    /////////////////////////////////////////////////////////////////////////
    // ADD ROW FUNCTION
    //
    // * When "Add Photo" clicked, this function is called without parameter.
    // * When page loaded for the first time, this function is called with value parameter
    //
    /////////////////////////////////////////////////////////////////////////
    function add_table_row_photos(value){

        var component = '<tr id="md_field_photos_tr_'+RECORD_INDEX_photos+'" class="md_field_photos_tr">';

        /////////////////////////////////////////////////////////////////////////////
        //    FIELD "index"
        /////////////////////////////////////////////////////////////////////////////
        var field_value = 0;
        if(typeof(value) != 'undefined' && value.hasOwnProperty('index')){
            field_value = value.index;
        }
        component += '<td>';
        component += '<input id="md_field_photos_col_index_'+RECORD_INDEX_photos+
              '" record_index="'+RECORD_INDEX_photos+
              '" class="md_field_photos_col md_field_photos_col_index form-control" column_name="index" type="text"'+
              ' name="md_field_photos_col_index_'+RECORD_INDEX_photos+'" value="'+field_value+'" />';
        component += '</td>';

        /////////////////////////////////////////////////////////////////////////////
        //    FIELD "url"
        /////////////////////////////////////////////////////////////////////////////
        var field_value = '';
        if(typeof(value) != 'undefined' && value.hasOwnProperty('url')){
            field_value = value.url;
        }
        component += '<td>';
        if(field_value != ''){
            component += '<div class="_photo-preview" style="background-image:url(\''+UPLOAD_PATH+'thumb_'+field_value+'\')"></div>';
        }else{
            component += '<input id="md_field_photos_col_url_'+RECORD_INDEX_photos+
                  '" record_index="'+RECORD_INDEX_photos+
                  '" class="md_field_photos_col form-control" column_name="url" type="file"'+
                  ' name="md_field_photos_col_url_'+RECORD_INDEX_photos+'" value="'+field_value+'"/>';
        }

        component += '</td>';

        /////////////////////////////////////////////////////////////////////////////
        //    FIELD "caption"
        /////////////////////////////////////////////////////////////////////////////
        var field_value = '';
        if(typeof(value) != 'undefined' && value.hasOwnProperty('caption')){
            field_value = value.caption;
        }
        component += '<td>';
        component += '<textarea id="md_field_photos_col_caption_'+RECORD_INDEX_photos+
              '" record_index="'+RECORD_INDEX_photos+
              '" class="md_field_photos_col md_field_photos_col_caption form-control" column_name="caption"'+
              ' name="md_field_photos_col_caption_'+RECORD_INDEX_photos+'">'+field_value+'</textarea>';
        component += '</td>';



        /////////////////////////////////////////////////////////////////////////////
        // Delete Button
        /////////////////////////////////////////////////////////////////////////////
        component += '<td><input class="md_field_photos_delete btn btn-default" record_index="'+RECORD_INDEX_photos+'" primary_key="" type="button" value="Delete" /></td>';
        component += '</tr>';

        /////////////////////////////////////////////////////////////////////////////
        // Add component to table
        /////////////////////////////////////////////////////////////////////////////
        $('#md_table_photos tbody').append(component);
        mutate_input();

        //$('#md_field_photos_col_caption_'+RECORD_INDEX_photos).ckeditor({toolbar:'mini',width:500});
        /*
        $('#md_field_photos_col_caption_'+RECORD_INDEX_photos).ace({
            theme: "eclipse",
            lang: "html",
            width: "500px",
            height: "100px"
        });
        */

    } // end of ADD ROW FUNCTION



    /////////////////////////////////////////////////////////////////////////
    // Main event handling program
    //
    // * Initialization
    // * md_field_photos_add.click (Add row)
    // * md_field_photos_delete.click (Delete row)
    // * md_field_photos_col.change (Edit cell)
    //
    /////////////////////////////////////////////////////////////////////////
    $(document).ready(function(){

        /////////////////////////////////////////////////////////////////////////////
        // INITIALIZATION
        /////////////////////////////////////////////////////////////////////////////
        synchronize_photos();
        for(var i=0; i<DATA_photos.update.length; i++){
            add_table_row_photos(DATA_photos.update[i].data);
            RECORD_INDEX_photos++;
        }


        /////////////////////////////////////////////////////////////////////////////
        // md_field_photos_add.click (Add row)
        /////////////////////////////////////////////////////////////////////////////
        $('#md_field_photos_add').click(function(){
            // new data
            var data = new Object();

            data.url     = '';
            data.caption = '';
            data.index   = 0;
            for(var i=0; i<DATA_photos.update.length; i++){
                if(parseInt(DATA_photos.update[i].data.index) >= data.index){
                    data.index = parseInt(DATA_photos.update[i].data.index) + 1;
                }
            }
            for(var i=0; i<DATA_photos.insert.length; i++){
                if(parseInt(DATA_photos.insert[i].data.index) >= data.index){
                    data.index = parseInt(DATA_photos.insert[i].data.index) + 1;
                }
            }
            // insert data to the DATA_photos
            DATA_photos.insert.push({
                'record_index' : RECORD_INDEX_photos,
                'primary_key' : '',
                'data' : data,
            });

            // add table's row
            add_table_row_photos(data);
            // add  by 1
            RECORD_INDEX_photos++;

            // synchronize to the md_real_field_photos_col
            synchronize_photos();
        });


        /////////////////////////////////////////////////////////////////////////////
        // md_field_photos_delete.click (Delete row)
        /////////////////////////////////////////////////////////////////////////////
        $('.md_field_photos_delete').live('click', function(){
            var record_index = $(this).attr('record_index');
            // remove the component
            $('#md_field_photos_tr_'+record_index).remove();

            var record_index_found = false;
            for(var i=0; i<DATA_photos.insert.length; i++){
                if(DATA_photos.insert[i].record_index == record_index){
                    record_index_found = true;
                    // delete element from insert
                    DATA_photos.insert.splice(i,1);
                    break;
                }
            }
            if(!record_index_found){
                for(var i=0; i<DATA_photos.update.length; i++){
                    if(DATA_photos.update[i].record_index == record_index){
                        record_index_found = true;
                        var primary_key = DATA_photos.update[i].primary_key;
                        // delete element from update
                        DATA_photos.update.splice(i,1);
                        // add it to delete
                        DATA_photos.delete.push({
                            'record_index':record_index,
                            'primary_key':primary_key
                        });
                        break;
                    }
                }
            }
            synchronize_photos();
        });


        /////////////////////////////////////////////////////////////////////////////
        // md_field_photos_col.change (Edit cell)
        /////////////////////////////////////////////////////////////////////////////
        $('.md_field_photos_col').live('change', function(){
            var value = $(this).val();
            var old_value = null;
            var column_name = $(this).attr('column_name');
            var record_index = $(this).attr('record_index');
            var record_index_found = false;
            // date picker
            if($(this).hasClass('datepicker-input')){
                value = js_date_to_php(value);
            }
            if(typeof(value)=='undefined'){
                value = '';
            }
            // change DATA_photos
            for(var i=0; i<DATA_photos.insert.length; i++){
                if(DATA_photos.insert[i].record_index == record_index){
                    record_index_found = true;
                    // insert value
                    eval('old_value = DATA_photos.insert['+i+'].data.'+column_name+';');
                    eval('DATA_photos.insert['+i+'].data.'+column_name+' = '+JSON.stringify(value)+';');
                    break;
                }
            }
            if(!record_index_found){
                for(var i=0; i<DATA_photos.update.length; i++){
                    if(DATA_photos.update[i].record_index == record_index){
                        record_index_found = true;
                        // edit value
                        eval('old_value = DATA_photos.update['+i+'].data.'+column_name+';');
                        eval('DATA_photos.update['+i+'].data.'+column_name+' = '+JSON.stringify(value)+';');
                        break;
                    }
                }
            }
            // if the changed column is index, perform swap value
            if(column_name == 'index'){
                for(var i=0; i<DATA_photos.insert.length; i++){
                    if(parseInt(DATA_photos.insert[i].data.index) == parseInt(value)){
                        var other_record_index = DATA_photos.insert[i].record_index;
                        if(other_record_index == record_index){continue;}
                        DATA_photos.insert[i].data.index = old_value;
                        $('#md_field_photos_col_index_'+other_record_index).val(old_value);
                    }
                }
                for(var i=0; i<DATA_photos.update.length; i++){
                    if(parseInt(DATA_photos.update[i].data.index) == parseInt(value)){
                        var other_record_index = DATA_photos.update[i].record_index;
                        if(other_record_index == record_index){continue;}
                        DATA_photos.update[i].data.index = old_value;
                        $('#md_field_photos_col_index_'+other_record_index).val(old_value);
                    }
                }
            }
            synchronize_photos();
        });


    });

    /////////////////////////////////////////////////////////////////////////
    // reset field on save
    /////////////////////////////////////////////////////////////////////////
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url == "{{ module_site_url }}manage_article/index/insert") {
            response = $.parseJSON(xhr.responseText);
            if(response.success == true){
                DATA_photos = {update:new Array(), insert:new Array(), delete:new Array()};
                $('#md_table_photos tr').not(':first').remove();
                synchronize_photos();
            }
        }
    });



    /////////////////////////////////////////////////////////////////////////
    // General Functions
    /////////////////////////////////////////////////////////////////////////

    // synchronize data to md_real_field_photos_col.
    function synchronize_photos(){
        $('#md_real_field_photos_col').val(JSON.stringify(DATA_photos));
    }

    function js_datetime_to_php(js_datetime){
        var datetime_array = js_datetime.split(' ');
        var js_date = datetime_array[0];
        var time = datetime_array[1];
        var php_date = js_date_to_php(js_date);
        return php_date + ' ' + time;
    }
    function php_datetime_to_js(php_datetime){
        var datetime_array = php_datetime.split(' ');
        var php_date = datetime_array[0];
        var time = datetime_array[1];
        var js_date = php_date_to_js(php_date);
        return js_date + ' ' + time;
    }

    function js_date_to_php(js_date){
        if(typeof(js_date)=='undefined' || js_date == ''){
            return '';
        }
        var date = '';
        var month = '';
        var year = '';
        var php_date = '';
        if(DATE_FORMAT == 'uk-date'){
            var date_array = js_date.split('/');
            day = date_array[0];
            month = date_array[1];
            year = date_array[2];
            php_date = year+'-'+month+'-'+day;
        }else if(DATE_FORMAT == 'us-date'){
            var date_array = js_date.split('/');
            day = date_array[1];
            month = date_array[0];
            year = date_array[2];
            php_date = year+'-'+month+'-'+day;
        }else if(DATE_FORMAT == 'sql-date'){
            var date_array = js_date.split('-');
            day = date_array[2];
            month = date_array[1];
            year = date_array[0];
            php_date = year+'-'+month+'-'+day;
        }
        return php_date;
    }


    function php_date_to_js(php_date){
        if(typeof(php_date)=='undefined' || php_date == ''){
            return '';
        }
        var date_array = php_date.split('-');
        var year = date_array[0];
        var month = date_array[1];
        var day = date_array[2];
        if(DATE_FORMAT == 'uk-date'){
            return day+'/'+month+'/'+year;
        }else if(DATE_FORMAT == 'us-date'){
            return month+'/'+date+'/'+year;
        }else if(DATE_FORMAT == 'sql-date'){
            return year+'-'+month+'-'+day;
        }else{
            return '';
        }
    }

    function IsNumeric(input){
        return (input - 0) == input && input.length > 0;
    }

    function mutate_input(){
        // datepikcer-input
        $('#md_table_photos .datepicker-input').datepicker({
                dateFormat: js_date_format,
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true
        });
        // date-picker-input-clear
        $('#md_table_photos .datepicker-input-clear').click(function(){
            $(this).parent().find('.datepicker-input').val('');
            return false;
        });
        // chzn-select
        $("#md_table_photos .chzn-select").chosen({allow_single_deselect: true});
        // numeric
        $('#md_table_photos .numeric').numeric();
        $('#md_table_photos .numeric').keydown(function(e){
            if(e.keyCode == 38)
            {
                if(IsNumeric($(this).val()))
                {
                    var new_number = parseInt($(this).val()) + 1;
                    $(this).val(new_number);
                }else if($(this).val().length == 0)
                {
                    var new_number = 1;
                    $(this).val(new_number);
                }
            }
            else if(e.keyCode == 40)
            {
                if(IsNumeric($(this).val()))
                {
                    var new_number = parseInt($(this).val()) - 1;
                    $(this).val(new_number);
                }else if($(this).val().length == 0)
                {
                    var new_number = -1;
                    $(this).val(new_number);
                }
            }
            $(this).trigger('change');
        });

    }

</script>
