<?php
// google_drive_form_api.php
class GoogleDriveFormAPI {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $refreshToken;
    private $accessToken;
    private $lastError;
 
    public function __construct() {
        $this->clientId = '390215650034-deaa705rg705116knsj80ffkbrp8k8id.apps.googleusercontent.com';
        $this->clientSecret = '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';
        $this->redirectUri = 'https://developers.google.com/oauthplayground';
        $this->refreshToken = '1//04Ax0oxKFumclCgYIARAAGAQSNwF-L9Ir0DTXvqZChjQBSy2xVNNdpZzLYxLFRmjE8FoiXhfYcLappd1BaAl0S7hRSILsRAx3yjI';
        $this->lastError = '';
    }
    
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Get access token using refresh token
     */
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://oauth2.googleapis.com/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token'
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode !== 200) {
            $this->lastError = "Failed to get access token. HTTP: $httpCode";
            return false;
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            $this->lastError = "No access token in response";
            return false;
        }
        
        $this->accessToken = $tokenData['access_token'];
        return $this->accessToken;
    }
    
    /**
     * Create a folder in Google Drive
     */
    public function createFolder($folderName, $parentFolderId = null) {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) return false;
            
            $metadata = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ];
            
            if ($parentFolderId) {
                $metadata['parents'] = [$parentFolderId];
            }
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://www.googleapis.com/drive/v3/files",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($metadata),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200) {
                $this->lastError = "Failed to create folder. HTTP: $httpCode";
                return false;
            }
            
            $result = json_decode($response, true);
            return $result['id'];
            
        } catch (Exception $e) {
            $this->lastError = "Create folder error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Find existing folder
     */
    public function findFolder($folderName, $parentFolderId = null) {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) return false;
            
            $parentId = $parentFolderId ?: 'root';
            $query = "name='" . addslashes($folderName) . "' and '" . $parentId . "' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://www.googleapis.com/drive/v3/files?" . http_build_query(['q' => $query, 'fields' => 'files(id,name)']),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken
                ]
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200) return false;
            
            $result = json_decode($response, true);
            $files = $result['files'] ?? [];
            
            return !empty($files) ? $files[0]['id'] : false;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get or create folder hierarchy: Root -> Tour Name -> surname_othername
     */
    public function setupFolderStructure($tourName, $surname, $otherName) {
        try {
            // Step 1: Get or create tour folder in root
            $tourFolderId = $this->findFolder($tourName);
            if (!$tourFolderId) {
                $tourFolderId = $this->createFolder($tourName);
                if (!$tourFolderId) {
                    throw new Exception("Failed to create tour folder: " . $this->getLastError());
                }
            }
            
            // Step 2: Get or create user folder inside tour folder
            $userFolderName = $surname . "_" . str_replace(' ', '_', $otherName);
            $userFolderId = $this->findFolder($userFolderName, $tourFolderId);
            if (!$userFolderId) {
                $userFolderId = $this->createFolder($userFolderName, $tourFolderId);
                if (!$userFolderId) {
                    throw new Exception("Failed to create user folder: " . $this->getLastError());
                }
            }
            
            return [
                'tour_folder_id' => $tourFolderId,
                'user_folder_id' => $userFolderId,
                'user_folder_name' => $userFolderName
            ];
            
        } catch (Exception $e) {
            $this->lastError = "Setup folder structure error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Upload a file to Google Drive
     */
    public function uploadFile($filePath, $fileName, $folderId = null) {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: $filePath");
            }
            
            $accessToken = $this->getAccessToken();
            if (!$accessToken) throw new Exception("Failed to get access token");
            
            $mimeType = $this->getMimeType($filePath);
            
            $metadata = [
                'name' => $fileName,
                'mimeType' => $mimeType
            ];
            
            if ($folderId) {
                $metadata['parents'] = [$folderId];
            }
            
            $boundary = '-------314159265358979323846';
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $closeDelimiter = "\r\n--" . $boundary . "--";
            
            $fileContent = file_get_contents($filePath);
            
            $postData = $delimiter .
                'Content-Type: application/json' . "\r\n\r\n" .
                json_encode($metadata) . $delimiter .
                'Content-Type: ' . $mimeType . "\r\n\r\n" .
                $fileContent . $closeDelimiter;
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: multipart/related; boundary="' . $boundary . '"',
                    'Content-Length: ' . strlen($postData)
                ]
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200) {
                throw new Exception("Upload failed. HTTP: $httpCode");
            }
            
            $result = json_decode($response, true);
            
            // Generate public URL
            $this->makeFilePublic($result['id']);
            
            return [
                'file_id' => $result['id'],
                'filename' => $result['name'],
                'download_link' => "https://drive.google.com/uc?id=" . $result['id'],
                'view_link' => "https://drive.google.com/file/d/" . $result['id'] . "/view"
            ];
            
        } catch (Exception $e) {
            $this->lastError = "Upload error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Make file public
     */
    private function makeFilePublic($fileId) {
        try {
            $accessToken = $this->getAccessToken();
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://www.googleapis.com/drive/v3/files/$fileId/permissions",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'role' => 'reader',
                    'type' => 'anyone'
                ]),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ]
            ]);
            
            curl_exec($curl);
            curl_close($curl);
            
        } catch (Exception $e) {
            // Ignore permission errors
        }
    }
    
    /**
     * Get MIME type for a file
     */
    private function getMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'zip' => 'application/zip'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
?>
