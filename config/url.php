<?php 
  $baseURL = 'http://localhost/JAM_LYINGIN/';
  $patientDirectory = $baseURL . 'auth/action/patient/';
  
  $frontPageURL = $baseURL . 'front.php';

  $getPhysicalExamURL = $patientDirectory . 'patient_get_physical_exam_record.php';
  $getMedicalHistoryURL = $patientDirectory . 'patient_get_medical_history.php';
  $getObstetricalHistoryURL = $patientDirectory . 'patient_get_obstetrical_history.php';
  $getVAWRiskAssessmentURL = $patientDirectory . 'patient_get_vaw_risk_assessment.php';
  $getAppointmentRequestsURL = $patientDirectory . 'patient_get_appointment_requests.php';
  $getVisitAnalyticsURL = $patientDirectory . 'patient_get_visit_analytics.php';

  //Clerk URLs
  $clerkDirectory = $baseURL . 'auth/action/clerk/';
  $getClerkPatientRecordURL = $clerkDirectory . 'clerk_get_patient_record.php';

?>
