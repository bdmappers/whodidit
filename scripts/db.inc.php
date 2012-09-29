<? # Some useful functions for whodidit scripts. Written by Ilya Zverev, licensed WTFPL.
$tile_size = 0.01;
$frontend_url = 'http://localhost/wdi/index.html';

function connect() {
    # host, user, password, database
    $db = new mysqli('localhost', 'wdi', '', 'wdi');
    $db->set_charset('utf8');
    return $db;
}

function parse_bbox( $bbox_str ) {
    global $tile_size;
    if( !preg_match('/^-?[\d.]+(,-?[\d.]+){3}$/', $bbox_str) ) return 0;
    $bbox = explode(',', $bbox_str);
    for( $i = 0; $i < 4; $i++ )
        $bbox[$i] = floor($bbox[$i]/$tile_size);
    if( $bbox[2] < $bbox[0] ) { $t = $bbox[2]; $bbox[2] = $bbox[0]; $bbox[0] = $t; }
    if( $bbox[3] < $bbox[1] ) { $t = $bbox[3]; $bbox[3] = $bbox[1]; $bbox[1] = $t; }
    return $bbox;
}

function is_changeset_suspicious( $ch ) {
    // more than 30% of node or way deletions (allow 11 and 6)
    if( $ch['nodes_deleted'] > $ch['nodes_modified'] + $ch['nodes_created'] + 10 ) return true;
    if( $ch['ways_deleted'] > $ch['ways_modified'] + $ch['ways_created'] + 5 ) return true;
    // more relations deleted than created (allow 3)
    if( $ch['relations_deleted'] > $ch['relations_created'] + 2 ) return true;
    // mass-change/deletion
    if( $ch['nodes_modified'] + $ch['nodes_deleted'] + $ch['ways_modified'] + $ch['ways_deleted'] > 5000 ) return true;
    if( $ch['relations_modified'] + $ch['relations_deleted'] > 40 ) return true;
    // potlatch + ways or relations modified/deleted
    if( strpos($ch['created_by'], 'Potlatch') !== FALSE && $ch['ways_modified'] + $ch['ways_deleted'] + $ch['relations_modified'] + $ch['relations_deleted'] > 0 ) return true;
    // well, seems normal
    return false;
}
?>
