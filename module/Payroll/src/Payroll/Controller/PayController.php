<?php

namespace Payroll\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Payroll\Model\Pay;
use Payroll\Model\PayDeduction;

class PayController extends AbstractActionController
{
  protected $payTable;
  protected $personnelTable;
  protected $deductionTable;
  protected $payDeductionTable;

  /*
  Corresponds to the index of the route.
  */
  public function indexAction()
  {

    //Auto calculate current period based current date time
    $startDate = date_create(date('Y').'-01-01');
    $today = date_create(date('Y-m-d'));
    $periodDuration = 14;
    $difference = (date_diff($startDate,$today)->format("%a"));
    $currentPeriod = ((int)floor($difference/14))+1;
    $lastPeriod = $currentPeriod-1;


    /*
    With Zend Framework 2, in order to set variables in the view, we return a ViewModel instance where the first parameter
    of the constructor is an array from the action containing data we need. These are then automatically passed to the view
    script. The ViewModel object also allows us to change the view script that is used, but the default is to use
    {controller name}/{action name}. We can now fill in the index.phtml view script.
    */
    return new ViewModel(array(
      'pays' => $this->getPayTable()->fetchAll($lastPeriod),
      'personnelTable' => $this->getPersonnelTable(),
    ));
  }

  public function payslipAction()
  {
    $id = (int) $this->params()->fromRoute('id',0);
    /* If id from route/action/id is zero redirect to index table. */
    if (!$id) {
      return $this->redirect()->toRoute('pay',array(
        'action' => 'index'
      ));
    }


    try {
      $pay = $this->getPayTable()->getPay($id);
    }
    catch (\Exception $ex) {
      return $this->redirect()->toRoute('pay',array(
        'action' => 'index'
      ));
    }

    $buffer = "";
    $viewablePayslipFile = fopen('./payslips/personnel_'.$pay->personnelId.'_period_'.$pay->period.'_'.$pay->year.'.dat','r');
    if ($viewablePayslipFile) {
      $buffer = fread($viewablePayslipFile,filesize('./payslips/personnel_'.$pay->personnelId.'_period_'.$pay->period.'_'.$pay->year.'.dat'));
      fclose($viewablePayslipFile);
    }

    /* Pass these key value pairings as identifiers into the edit view. */
    return array(
      'id' => $id,
      'buffer' => $buffer,
    );
  }

  public function addAction()
  {
    $id = (int) $this->params()->fromRoute('id',0);
    /* If id from route/action/id is zero redirect to route index. */
    if (!$id) {
      return $this->redirect()->toRoute('pay');
    }

    $request = $this->getRequest();
    /* If request method is POST and 'del' is Yes delete row from database table. */
    if ($request->isPost()) {
      // process stuff
      $id = $request->getPost('id');
      $pay = $this->getPayTable()->getPay($id);
      $personnelId = $pay->personnelId;
      $deductionId = $request->getPost('periodic_deduction');
      $duration = $request->getPost('duration');

      if (!$deductionId) {
        return $this->redirect()->toRoute('pay');
      }
      if (!$duration){
        $duration = 1;
      }

      try{
        $payDeduction = $this->getPayDeductionTable()->getPayDeduction2($personnelId,$deductionId);
      }
      catch (\Exception $ex) {
        $payDeduction = new PayDeduction();
        $payDeduction->payDeductionId = 0;
        $payDeduction->personnelId = $personnelId;
        $payDeduction->deductionId = $deductionId;
      }

      $payDeduction->duration = $duration - 1;
      $this->getPayDeductionTable()->savePayDeduction($payDeduction);
      $deduction = $this->getDeductionTable()->getDeduction($deductionId);
      $viewablePayslipFile = fopen('./payslips/personnel_'.$pay->personnelId .'_period_'.$pay->period.'_'.$pay->year.'.dat','a');
      if ($deduction->fixedAmount) {
        $pay->netAmount -= $deduction->fixedAmount;
        fwrite($viewablePayslipFile,$deduction->deductionName.' $'.$deduction->fixedAmount.',');
      }
      else {
        $pay->netAmount -= ($pay->grossAmount * ($deduction->deductionPercentage/100.00));
        fwrite($viewablePayslipFile,$deduction->deductionName.' '.$deduction->deductionPercentage.'%,');
      }
      $this->getPayTable()->savePay($pay);

      fclose($viewablePayslipFile);
      return $this->redirect()->toRoute('pay');
    }

    /* Pass these key value pairings as identifiers into the delete view. */
    return array(
      'id' => $id,
      'deductions' => $this->getDeductionTable()->fetchAll2(0),
    );
  }

  /*
  Corresponds to the action:delete of the route.
  */
  public function deleteAction()
  {
    $id = (int) $this->params()->fromRoute('id',0);
    /* If id from route/action/id is zero redirect to route index. */
    if (!$id) {
      return $this->redirect()->toRoute('pay');
    }

    $request = $this->getRequest();
    /* If request method is POST and 'del' is Yes delete row from database table. */
    if ($request->isPost()) {
      $del = $request->getPost('del','No');

      if ($del == 'Yes') {
        $id = (int) $request->getPost('id');
        $this->getPayTable()->deletePay($id);
      }

      return $this->redirect()->toRoute('pay');
    }

    /* Pass these key value pairings as identifiers into the delete view. */
    return array(
      'id' => $id,
      'pay' => $this->getPayTable()->getPay($id)
    );
  }

  /*
  This method uses the services manager to get an instance of a Table object to
  access data in the database. This instance can be used throughout the Controller
  without creating new instances.
  */
  public function getPayTable()
  {
    if (!$this->payTable) {
      $sm = $this->getServiceLocator();
      $this->payTable = $sm->get('Payroll\Model\PayTable');
    }
    return $this->payTable;
  }

  /*
  This method uses the services manager to get an instance of a Table object to
  access data in the database. This instance can be used throughout the Controller
  without creating new instances.
  */
  public function getPersonnelTable()
  {
    if (!$this->personnelTable) {
      $sm = $this->getServiceLocator();
      $this->personnelTable = $sm->get('Payroll\Model\PersonnelTable');
    }
    return $this->personnelTable;
  }

  /*
  This method uses the services manager to get an instance of a Table object to
  access data in the database. This instance can be used throughout the Controller
  without creating new instances.
  */
  public function getDeductionTable()
  {
    if (!$this->deductionTable) {
      $sm = $this->getServiceLocator();
      $this->deductionTable = $sm->get('Payroll\Model\DeductionTable');
    }
    return $this->deductionTable;
  }

  /*
  This method uses the services manager to get an instance of a Table object to
  access data in the database. This instance can be used throughout the Controller
  without creating new instances.
  */
  public function getPayDeductionTable()
  {
    if (!$this->payDeductionTable) {
      $sm = $this->getServiceLocator();
      $this->payDeductionTable = $sm->get('Payroll\Model\PayDeductionTable');
    }
    return $this->payDeductionTable;
  }

}
