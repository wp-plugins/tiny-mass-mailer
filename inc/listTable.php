<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class tinymassmailer_data_table extends WP_List_Table
{

    public $table_data = array() , $per_page = 3 , $all_rows_num , $tbl_start = 0;
    function __construct() {
        global $status, $page ,  $wpdb;

        parent::__construct( array(
            'singular' => 'send', /*singular name of the listed records*/
            'plural' => 'sends', /*plural name of the listed records*/
            'ajax' => false /*does this table support ajax?*/

        ) );
        $this->per_page = 50;
    }


    function no_items() {
        _e('No Item Found' , 'tinymassmailer');
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'subject':
            case 'text':
            case 'last_sent_time':
            case 'start_date':
            case 'end_date':
            case 'state':
            case 'opens':
            case 'clicks':
            case 'last_user_id':
                return $item[ $column_name ];
            default:
                //return print_r( $item, true ) ; /*Show the whole array for troubleshooting purposes*/
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(

            'id' => array('id' , false),
            'subject' => array('subject' , false),
            'text' => array('text' , false),
            'last_sent_time' => array('last_sent_time' , false),
            'start_date' => array('start_date' , false),
            'end_date' => array('end_date' , false),
            'state' => array('state' , false),
            'opens' => array('opens' , false),
            'clicks' => array('clicks' , false),
            'last_user_id' => array('last_user_id' , false),
        );
        return $sortable_columns;
    }

    function get_columns() {
        $columns = array(
            'id' => __('id' , 'tinymassmailer'),
            'subject' => __('subject' , 'tinymassmailer'),
            'text' => __('text' , 'tinymassmailer'),
            'last_sent_time' => __('last_sent_time' , 'tinymassmailer'),
            'start_date' => __('start_date' , 'tinymassmailer'),
            'end_date' => __('end_date' , 'tinymassmailer'),
            'state' => __('state' , 'tinymassmailer'),
            'opens' => __('opens' , 'tinymassmailer'),
            'clicks' => __('clicks' , 'tinymassmailer'),
            'last_user_id' => __('last_user_id' , 'tinymassmailer'),


        );
        return $columns;
    }

    function usort_reorder( $a, $b ) {
        /* If no sort, default to title*/
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';
        /* If no order, default to asc*/
        $order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
        /* Determine sort order*/
        $result = strcmp( $a[$orderby], $b[$orderby] );
        /* Send final sort direction to usort*/
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function single_row( $a_comment ) {
        global $post, $comment;
        /*
        approved = admin approved
        unapproved = admin not approved beacuase of pays or incorrect documents
        new = new added users , wating for approve
        */
        // echo '<pre>';
        // var_dump($a_comment);
        // die();
        $reg = $a_comment['state'];


        /* check for pays */

        if($reg == 'completed')
        {
            $the_comment_class = 'completed';
        }
        if($reg == 'new')
        {
            $the_comment_class = 'new';
        }
        if($reg == 'canceled')
        {
            $the_comment_class = 'canceled';
        }


        echo "<tr id='comment-".$a_comment['id']."' class='send-$the_comment_class'>";
        echo $this->single_row_columns( $a_comment );
        echo "</tr>\n";
    }


    function column_subject( $item ) {
        $actions = array(
            'delete' => sprintf( '<a class="confirmdelete" href="?page=%s&action=%s&send=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'] ),
        );
        if($item['state'] == 'new'){
            $actions['subject-cancel'] =  sprintf( '<a class="confirmdelete" href="?page=%s&action=%s&send=%s">cancel</a>', $_REQUEST['page'], 'cancel', $item['id'] );
        }

        if($item['state'] == 'canceled'){
            $actions['subject-resend'] =  sprintf( '<a class="confirmdelete" href="?page=%s&action=%s&send=%s">resend</a>', $_REQUEST['page'], 'resend', $item['id'] );
        }

        return sprintf( '%1$s %2$s', $item['subject'], $this->row_actions( $actions ) );
    }

    function column_text( $item ) {
        $actions = array(
            'show full text' => sprintf( '<a data-full="'.$item['text'].'" class="confirmshow-full-text" href="?page=%s&action=%s&send=%s">show full text</a>', $_REQUEST['page'], 'show full text', $item['id'] ),
        );


        return sprintf( '%1$s %2$s', $item['text'], $this->row_actions( $actions ) );
    }

    function prepare_items() {
        global $wpdb;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $per_page =  $this->per_page;
        $current_page = $this->get_pagenum();
        $tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
        /*populate table*/
        $fetch_query 		=	'SELECT * FROM '.$tiniymassm_sends;
        $row_count_query 	=	'SELECT count(*) as cnt FROM '.$tiniymassm_sends;
        $this->tbl_start = ( $current_page-1 )* $per_page;
        $fetch_query 	.=	' ORDER BY id DESC LIMIT '.$this->tbl_start.','.$this->per_page;
        $rows 					= $wpdb->get_results($fetch_query);
        $all_rows_num 			= $wpdb->get_results($row_count_query);
        $this->all_rows_num 	= $all_rows_num[0]->cnt;


        // echo '<pre>';
        foreach ($rows as $row) {
			$end_date = '';
			$last_sent_time = '';
			if(trim($row->end_date) == ''){
				$end_date = 'در حال ارسال';
			}else{
				$end_date = (function_exists('jdate')?(jdate('l jS F Y h:i:s A' , mysql2date('G' , $row->end_date ))):$row->end_date);
			}
			
			if(trim($row->last_sent_time) == ''){
				$last_sent_time = 'در صف ارسال';
			}else{
				$last_sent_time = (function_exists('jdate')?(jdate('l jS F Y h:i:s A' , $row->last_sent_time)):$row->last_sent_time);
			}
			
			
            $this->items[] =array(
                'id'                => $row->id , 
                'subject'           => $row->subject , 
                'text'              =>  wp_trim_words(stripcslashes($row->text) , 3 ).'...' ,
                'last_sent_time'    => $last_sent_time , 
                'start_date'        => (function_exists('jdate')?(jdate('l jS F Y h:i:s A' , mysql2date('G' , $row->start_date ))):$row->start_date) , 
                'end_date'          => $end_date , 
                'state'             => $row->state , 
                'opens'             => $row->opens , 
                'clicks'            => $row->clicks , 
                'last_user_id'      => $row->last_user_id , 

            ) ;
        }

        $this->set_pagination_args( array(
            'total_items' => $this->all_rows_num,
            'per_page' => $this->per_page
        ) );

    }

} /*////#class////*/







class tinymassmailer_data_table_url extends WP_List_Table
{

    public $table_data = array() , $per_page = 3 , $all_rows_num , $tbl_start = 0;
    function __construct() {
        global $status, $page ,  $wpdb;

        parent::__construct( array(
            'singular' => 'url', /*singular name of the listed records*/
            'plural' => 'urls', /*plural name of the listed records*/
            'ajax' => false /*does this table support ajax?*/

        ) );
        $this->per_page = 50;
    }


    function no_items() {
        _e('No Item Found' , 'tinymassmailer');
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'url':
            case 'hits':
                return $item[ $column_name ];
            default:
                //return print_r( $item, true ) ; /*Show the whole array for troubleshooting purposes*/
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(

            'id' => array('id' , false),
            'url' => array('url' , false),
            'hits' => array('hits' , false),
        );
        return $sortable_columns;
    }

    function get_columns() {
        $columns = array(
            'id' => __('id' , 'tinymassmailer'),
            'url' => __('url' , 'tinymassmailer'),
            'hits' => __('hits' , 'tinymassmailer'),


        );
        return $columns;
    }

    function usort_reorder( $a, $b ) {
        /* If no sort, default to title*/
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';
        /* If no order, default to asc*/
        $order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
        /* Determine sort order*/
        $result = strcmp( $a[$orderby], $b[$orderby] );
        /* Send final sort direction to usort*/
        return ( $order === 'asc' ) ? $result : -$result;
    }



    function prepare_items() {
        global $wpdb;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $per_page =  $this->per_page;
        $current_page = $this->get_pagenum();
        $tiniymassm_urls =$wpdb->prefix.'tiniymassm_urls';
        /*populate table*/
        $fetch_query        =   'SELECT * FROM '.$tiniymassm_urls;
        $row_count_query    =   'SELECT count(*) as cnt FROM '.$tiniymassm_urls;
        $this->tbl_start = ( $current_page-1 )* $per_page;
        $fetch_query    .=  ' ORDER BY id DESC LIMIT '.$this->tbl_start.','.$this->per_page;
        $rows                   = $wpdb->get_results($fetch_query);
        $all_rows_num           = $wpdb->get_results($row_count_query);
        $this->all_rows_num     = $all_rows_num[0]->cnt;


        // echo '<pre>';
        foreach ($rows as $row) {
            $this->items[] =array(
                'id'                => $row->id , 
                'url'           => $row->url , 
                'hits'           => $row->hits , 


            ) ;
        }

        $this->set_pagination_args( array(
            'total_items' => $this->all_rows_num,
            'per_page' => $this->per_page
        ) );

    }

} /*////#class////*/