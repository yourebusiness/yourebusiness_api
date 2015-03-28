<?php

class Admin extends CI_Controller {
	private $username = "";
	private $userId = 0;
	private $companyId = 0;
	private $role = "-1";	//enum("0, 1")

	public function __construct() {
		parent::__construct();

		if (!$this->session->userdata["username"])
			redirect(site_url("api/signin"));
		else {
			$this->username = $this->session->userdata["username"];
			$this->userId = $this->session->userdata["userId"];
			$this->companyId = $this->session->userdata["companyId"];
			$this->role = $this->session->userdata["role"];
		}
	}

	private function showMasseurs($arr) {
		$this->load->model("Admin_model");
		return $this->Admin_model->showMasseurs($arr);
	}

	public function index() {
		$data["title"] = "Welcome";
		$data["username"] = $this->username;
		$data["userRights"] = $this->session->userdata["role"];

		$this->load->view("templates/header", $data);
		$this->load->view("sessioned/home");

		// we want this to delete after exiting from company profile page.
		$this->load->helper("cookie");
		delete_cookie("yourspaFunc_CompanyProfile");
	}

	public function masseur() {
		$arr = array("companyId" => $this->companyId);
		
		$masseurs = $this->showMasseurs($arr);
		if ($masseurs)
			$data["masseurs"] = $masseurs;
		else
			$data["masseurs"] = array();

		$data["title"] = "Masseurs";
		$data["username"] = $this->username;
		$this->load->view("templates/header", $data);
		$this->load->view("sessioned/masseur_view", $data); // includes footer
	}

	public function masseuradd_view() {
		$data["title"] = "Add Masseur";
		$data["username"] = $this->username;
		$this->load->view("templates/header", $data);
		$this->load->view("sessioned/masseuradd_view");
	}

	public function masseuradd() {
		
		$midName = $this->input->get("midName");
		if ($midName == null || $midName == "")
			$midName = null;

		$data = array("companyId" => $this->session->userdata["companyId"],
					"createdBy" => $this->session->userdata["userId"],
					"nickname" => $this->input->get("nickname"),
					"fName" => $this->input->get("fName"),
					"midName" => $midName,
					"lName" => $this->input->get("lName")
				);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->masseurAdd($data))
			echo "Error adding new record.";
		else
			return true;
	}

	public function masseurChangeStatus($id, $status) {
		if ($status == "Y")
			$status = "N";
		else
			$status = "Y";

		$data = array("id" => $id, "status" => $status, "updatedBy" => $this->session->userdata["userId"]);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->masseurChangeStatus($data))
			echo "Error updating status";
		else
			return true;
	}

	public function masseurDelete($id) {
		$this->load->model("Admin_model");
		if (!$this->Admin_model->masseurDelete($id))
			echo "Error deleting masseur.";
		else
			return true;
	}

	public function masseurEdit($id = 0, $nickname = "", $fName = "", $midName = "", $lName = "") {
		$midName = $this->input->get("midName");
		if ($midName == null || empty($midName))
			$midName = null;

		$data = array("id" => $this->input->get("id"),
					"updatedBy" => $this->session->userdata["userId"],
					"nickname" => $this->input->get("nickname"),
					"fName" => $this->input->get("fName"),
					"midName" => $midName,
					"lName" => $this->input->get("lName")
				);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->masseurEdit($data))
			echo "Error adding new record.";
		else
			return true;
	}


	/* controller for users */
	private function getAllUsers() {
		$this->load->model("Admin_model");
		return $this->Admin_model->getAllUsers();
	}

	public function users() {
		$currentUserId = $this->session->userdata["userId"];
		$this->load->model("Admin_model");
		$data["users"] = $this->Admin_model->getAllUsersExceptCurrent($currentUserId);
		$this->load->helper("record");
		$data["userRights"] = checkUserRightsByUserId($currentUserId);
		$headerData["title"] = "Users";
		$headerData['username'] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/users_view", $data);
	}

	public function usersAdd_view() {
		$headerData["title"] = "Add Users";
		$data["username"] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/usersadd_view", $data);
	}

	public function usersAdd() {
		$midName = $this->input->get("midName");
		if ($midName == null || empty($midName))
			$midName = null;

		$address = $this->input->get("address");
		if ($address == null || empty($address))
			$address = null;

		$data = array("username" => $this->input->get("username"),
					"password" => $this->input->get("password"),
					"fName" => $this->input->get("fName"),
					"midName" => $midName,
					"lName" => $this->input->get("lName"),
					"address" => $address,
					"gender" => $this->input->get("gender"),
					"role" => $this->input->get("role"),
					"createdBy" => $this->session->userdata["userId"],
					"companyId" => $this->session->userdata["companyId"]
				);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->usersAdd($data))
			echo "Error adding new record.";
		else
			return true;
	}

	public function usersEdit() {
		//username,passwd,fName,midName,lName,email,address,gender,updatedBy

		$midName = $this->input->get("midName");
		if ($midName == null || empty($midName))
			$midName = null;
		$address = $this->input->get("address");
		if ($address == null || empty($address))
			$address = null;

		$data = array("username" => $this->input->get("username"),
					"fName" => $this->input->get("fName"),
					"midName" => $midName,
					"lName" => $this->input->get("lName"),
					"address" => $this->input->get("address"),
					"gender" => $this->input->get("gender"),
					"updatedBy" => $this->session->userdata["userId"]
				);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->usersEdit($data))
			echo "Error editting record.";
		else
			return true;
	}

	public function usersDelete($id) {
		$this->load->model("Admin_model");
		if (!$this->Admin_model->usersDelete($id))
			echo "Deleting record was not successful.";
		else
			return true;
	}

	public function usersChangeStatus($id, $status) {
		if ($status == "Y")
			$status = "N";
		else
			$status = "Y";

		$data = array("id" => $id, "status" => $status, "updatedBy" => $this->session->userdata["userId"]);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->usersChangeStatus($data))
			echo "Error updating status";
		else
			return true;
	}

	public function changeUserRights($id, $userRights) {
		/* administrator = 0; User = 1 */

		$userRights = strtolower($userRights);
		if ($userRights == "administrator")
			$userRights = 1;
		elseif ($userRights == 'user')
			$userRights = 0;
		else
			return false;

		$data = array("id" => $id, "role" => $userRights, "updatedBy" => $this->session->userdata["userId"]);

		$this->load->model("Admin_model");
		if (!$this->Admin_model->usersChangeRights($data))
			echo "Error updating status";
		else
			return true;
	}


	/* controller for services */

	public function getAllServices() {		
		$this->load->model("Admin_model");
		$result = $this->Admin_model->getAllServices();
		header('Content-type: application/json');
		echo json_encode($result);
	}

	public function services() {
		$this->load->helper("record");
		$data["userRights"] = checkUserRightsByUserId($this->session->userdata["userId"]);

		if ($data["userRights"] == 0) { // 0 = administrator
			$headerData["title"] = "Services";
			$headerData['username'] = $this->username;
			$this->load->view("templates/header", $headerData);
			$this->load->view("sessioned/services_view");
		} else {
			header("HTTP/1.1 401 Unauthorized.");
			echo "Unauthorized.<br />Go to <a href='" . base_url("admin") . "''>Home Page</a>";
		}
	}

	public function deleteService($id = 0) {
		$id = $this->input->get("id");
		if (empty($id) || $id == "")
			return false;

		$this->load->model("Admin_model");
		$this->Admin_model->serviceDelete($id);
	}

	public function addService_view() {
		$data["userRights"] = $this->session->userdata["role"];
		if ($data["userRights"] == 0) {
			$headerData["title"] = "Add new Services";
			$headerData["username"] = $this->username;
			$data["userRights"] = $this->session->userdata["role"];
			$this->load->view("templates/header", $headerData);
			$this->load->view("sessioned/addService_view", $data);
		} else {
			header("HTTP/1.1 401 Unauthorized.");
			echo "Unauthorized.<br />Go to <a href='" . base_url("admin") . "''>Home Page</a>";
		}
	}

	private function checkDataForAdd($data) {
		if (empty($data["serviceName"]) || $data["regPrice"] == "" || $data["memberPrice"] == "" || empty($data["createdBy"]))
			return false;

		return true;
	}

	public function addService() {
		$data = array("companyId" => $this->session->userdata["companyId"],
					"serviceName" => $this->input->get("serviceName"),
					"description" => $this->input->get("description"),
					"regPrice" => $this->input->get("regPrice"),
					"memberPrice" => $this->input->get("memberPrice"),
					"createdBy" => $this->session->userdata["userId"]
				);

		if (!$this->checkDataForAdd($data))
			return false;
		
		$this->load->model("Admin_model");
		if (!$this->Admin_model->addService($data))
			return FALSE;
		else
			return TRUE;
	}

	private function getServiceDetailById($id) {
		$this->load->model("Admin_model");
		return $this->Admin_model->getServiceDetailById($id);
	}

	public function editService_view() {
		$id = $this->input->get("id");

		if (empty($id) || $id == "")
			$id = 0;

		$headerData["title"] = "Edit Service Info";
		$headerData["username"] = $this->username;

		$data['serviceDetails'] = $this->getServiceDetailById($id);

		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/editService_view", $data);
	}

	public function editService() {
		$data = array("serviceId" => $this->input->get("serviceId"),
					"serviceName" => $this->input->get("serviceName"),
					"description" => $this->input->get("description"),
					"regPrice" => $this->input->get("regPrice"),
					"memberPrice" => $this->input->get("memberPrice"),
					"createdBy" => $this->session->userdata["userId"]
				);

		$this->load->model("Admin_model");
		$this->Admin_model->editService($data);
	}
	/* end for services */

	/* profile */
	public function profile() {
		$data["userId"] = $this->session->userdata["userId"];
		$this->load->model("Admin_model");
		$profileDetails = $this->Admin_model->profile($data["userId"]); //returns array otherwise false.
		if($profileDetails)
			$data["profileDetails"] = $profileDetails;
		else
			$data["profileDetails"] = array();

		$headerData["title"] = "Profile";
		$headerData['username'] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/profile_view", $data);
	}	

	public function updateProfile() {
		$data = array("userId" => $this->input->get("userId"),
					"username" => $this->input->get("username"),
					"fName" => $this->input->get("fName"),
					"midName" => $this->input->get("midName"),
					"lName" => $this->input->get("lName"),
					"gender" => $this->input->get("gender"),
					"address" => $this->input->get("address"),
					"updatedBy" => $this->session->userdata["userId"]
				);

		$this->load->model("Admin_model");
		$this->Admin_model->updateProfile($data);
	}

	public function changePassword() {
		$oldPassword = $this->input->post("oldPassword");
		$newPassword = $this->input->post("newPassword");
		$confirmPassword = $this->input->post("confirmPassword");

		if ($newPassword !== $confirmPassword)
			return false;

		$data = array("userId" => $this->session->userdata["userId"],
					"newPassword" => $newPassword,
					"oldPassword" => $oldPassword
				);
		$this->load->model("Admin_model");
		if ($this->Admin_model->changePassword($data))
			$this->session->sess_destroy();
	}

	public function changePasswordView() {
		$data["userId"] = $this->session->userdata["userId"];
		$headerData["title"] = "Change Password";
		$headerData['username'] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/changePassword_view", $data);
	}

	/* Subscription methods */
	
	public function addSubscription() {
		$data = array("companyId" => 1,
					"paymentId" => 1,
					"stripeToken" => "sk_test_BQokikJOvBiI2HlWgH4olfQ2",
					"createdBy" => 1
				);
		$this->load->model("Subscription_model");
		if (!$this->Subscription_model->add($data))
			return FALSE;
		else
			return TRUE;
	}

	/* End for subscription methods*/

	/* transaction functions */

	public function transactions() {
		$this->load->model("Transactions_model");
		$subscription = $this->Transactions_model->withActiveSubscription($this->companyId);
		
		$headerData["title"] = "Transactions";
		$headerData["username"] = $this->username;
		$headerData["userRights"] = $this->role;

		$this->load->model("Employee_model");
		$this->load->model("Customers_model");
		$this->load->model("Services_model");
		$data["masseurs"] = $this->Employee_model->getMasseurNamesByCompanyId($this->companyId);
		$data["services"] = $this->Services_model->getServicesByCompanyId($this->companyId);
		$data["customers"] = $this->Customers_model->getCustomersByCompanyId($this->companyId);
		$data["companyId"] = $this->companyId;

		$this->load->view("templates/header", $headerData);
		if ($subscription)
			$this->load->view("sessioned/transaction_view", $data);
		else
			$this->load->view("sessioned/alertSubscription_view");
	}

	public function addTransaction() {
		$remarks = $this->input->get("remarks");
		( ! isset($remarks)) ? $remarks = NULL : $remarks;

		$data = array("companyId" => $this->companyId,
			"serviceId" => $this->input->get("serviceId"),
			"serviceName" => $this->input->get("serviceName"),
			"customerId" => $this->input->get("customerId"),
			"customerName" => $this->input->get("customerName"),
			"employeeId" => $this->input->get("employeeId"),
			"price" => $this->input->get("price"),
			"discount" => 0, // we don't support this one yet
			"total" => $this->input->get("price"), // we don't support discount yet for now
			"createdBy" => $this->userId,
			"remarks" => $remarks, // we don't support this one yet
			);

		$this->load->model("Transactions_model");
		if ($this->Transactions_model->add($data))
			return TRUE;
		else
			return FALSE;
	}

	public function getPriceForCustomer() {
		$data = array("serviceId" => $this->input->get("serviceId"),
					"customerId" => $this->input->get("customerId"),
					"companyId" => $this->companyId,
				);
		
		$this->load->helper("record");
		header("Content-type: application/json");
		$price = getPriceForCustomer($data);
		if ($price === FALSE)
			$price = "0.00";

		echo json_encode($price);
	}

	public function successaddtransaction() {
		$headerData["title"] = "Transactions";
		$headerData["username"] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/successAddTransaction_view");
	}

	/* end for transactions */

	/* controller for customer */

	public function getCustomersDetails() {
		$searchText = $this->input->get("searchText");
		$companyId = $this->session->userdata["companyId"];
		$data = array("companyId" => $companyId, "searchText" => $searchText);
		$this->load->model("Admin_model");
		header("Content-type: application/json");
		echo json_encode($this->Admin_model->searchCustomersDetails($data));
	}

	public function customers() {
		$headerData["title"] = "Customers";
		$headerData['username'] = $this->username;
		$companyId = $this->session->userdata["companyId"];
		$this->load->model("Admin_model");
		$data["customers"] = $this->Admin_model->getAllCustomersDetails($companyId);
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/customers_view", $data);
	}

	public function addCustomer_view() {
		$headerData["title"] = "AddCustomers";
		$headerData['username'] = $this->username;
		$this->load->view("templates/header", $headerData);
		$this->load->view("sessioned/addCustomer_view");
	}

	public function addCustomer() {
		$data = array("companyId" => $this->session->userdata["companyId"],
					"custType" => $this->input->get("custType"),
					"fName" => $this->input->get("fName"),
					"midName" => $this->input->get("midName"),
					"lName" => $this->input->get("lName"),
					"createdBy" => $this->session->userdata["userId"]
				);
		$this->load->model("Admin_model");
		if ($this->Admin_model->addCustomer($data))
			return TRUE;
		else
			return FALSE;
	}

	public function searchCustomers($searchText = "") {
		header("Content-type: application/json");
		//if ($searchText == "")


	}

	/* end for customer controller */

	/* controller for company profile */

	public function companyProfile() {
		$cookie = $this->input->cookie("yourspaFunc_CompanyProfile");
		if (password_verify($this->session->userdata["username"], $cookie)) {
			$headerData["title"] = "Company";
			$headerData['username'] = $this->username;
			$data["companyId"] = $this->session->userdata["companyId"];
			$this->load->model("Api_model");
			$data["provinces"] = $this->Api_model->getProvince(); // get all province list

			$this->load->model("Admin_model");
			$provinceId = $this->Admin_model->getProvinceIdByCompanyId($data["companyId"])[0]["province"];

			$data["cities"] = $this->Api_model->getCity($provinceId);

			$data["companyInfo"] = $this->Admin_model->getCompanyInfo($data["companyId"]);
			$this->load->view("templates/header", $headerData);
			$this->load->view("sessioned/company_view", $data);
		} else {
			redirect("admin/login?v=companyProfile");
		}
	}

	// we have to avoid hacking here.
    private function assertEqualCompanyId($companyId) {
        if ($companyId !== $this->session->userdata["companyId"])
            return false;

        return true;
    }

    public function editCompanyProfile() {
        $cookie = $this->input->cookie("yourspaFunc_CompanyProfile");
		if (password_verify($this->session->userdata["username"], $cookie)) {
            $data = array();
            $data["companyId"] = $this->input->get("companyId");

            if (!$this->assertEqualCompanyId($data["companyId"]))
                return false;

            $data["company"] = $this->input->get("company");
            $data["province"] = $this->input->get("province");
            $data["city"] = $this->input->get("city");
            $data["address"] = $this->input->get("address");
            $data["phoneNo"] = $this->input->get("phoneNo");
            $data["tin"] = $this->input->get("tin");
            $data["companyWebsite"] = $this->input->get("companyWebsite");

            $this->load->model("Register_model");
            if ($this->Register_model->edit($data)) {
                $headerData["title"] = "Update Company Profile";
    			$headerData['username'] = $this->username;
                $this->load->view("templates/header", $headerData);
                $this->load->view("sessioned/editCompanyProfileSuccess_view");
            }
        } else { // load the login page
            $data["title"] = "Update Company Profile";
            $this->load->view("templates/header", $data);
            $this->load->view("sessioned/login");
        }

    }

    public function login() {
    	if ($this->input->get("v") == "companyProfile") {
    		$headerData["title"] = "Login";
    		$headerData['username'] = $this->username;
	        $this->load->view("templates/header", $headerData);
	        $this->load->view("sessioned/login");
    	}        
    }

    public function checkLogin() {
    	$v = trim($this->input->post("v"));
    	if ($v == "") {
    		redirect(base_url("admin"));
    		exit(0);
    	}

    	// current user should match with what the user input.
    	$username = $this->input->post("username");
    	if ($username != $this->session->userdata["username"]) {
    		redirect(base_url("admin"));
    		exit(0);
    	}
    	
    	$data = array("username" => $username, "password" => $this->input->post("password"));
    	if ($v == "companyProfile") {
    		$this->load->model("Admin_model");
    		if ($this->Admin_model->login($data)) {
    			$this->load->helper("cookie");
    			// we add new session data to know what are we logging into
    			$cookieValue = password_hash($username, PASSWORD_BCRYPT);
				$cookie = array(
					    'name'   => 'yourspaFunc_CompanyProfile',
					    'value'  => $cookieValue,
					    'expire' => '600',
					    'secure' => FALSE
					);

					$this->input->set_cookie($cookie);
    		}
    	}
    }

} /* end of admin.php class */