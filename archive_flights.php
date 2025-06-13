<?php
function archiveCompletedFlights($conn) {
    // Set timezone to Pakistan timezone
    date_default_timezone_set('Asia/Karachi');
    
    // Select all flights where arrival time is less than or equal to current time
    $select_sql = "SELECT * FROM flights WHERE arrival <= NOW()";
    
    // Log archival check (optional, remove if not needed)
    error_log("Checking for flights to archive at: " . date('Y-m-d H:i:s'));
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Prepare archive insertion statement
        $archive_sql = "INSERT INTO flight_archives (flight_no, airline, route, departure, arrival, gate, status, archived_at, archive_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $archive_stmt = $conn->prepare($archive_sql);
        
        // Prepare delete statement
        $delete_sql = "DELETE FROM flights WHERE flight_no = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        
        while ($flight = $result->fetch_assoc()) {
            // Archive the flight
            $current_datetime = date('Y-m-d H:i:s');
            $archive_reason = "Flight completed - Arrival time passed";
            
            $archive_stmt->bind_param("sssssssss", 
                $flight['flight_no'],
                $flight['airline'],
                $flight['route'],
                $flight['departure'],
                $flight['arrival'],
                $flight['gate'],
                $flight['status'],
                $current_datetime,
                $archive_reason
            );
            
            // Execute archive and delete operations in a transaction
            $conn->begin_transaction();
            
            try {
                $archive_stmt->execute();
                
                $delete_stmt->bind_param("s", $flight['flight_no']);
                $delete_stmt->execute();
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Error archiving flight " . $flight['flight_no'] . ": " . $e->getMessage());
            }
        }
        
        $archive_stmt->close();
        $delete_stmt->close();
    }
    
    $select_stmt->close();
}
?>
