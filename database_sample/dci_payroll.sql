create database if not exists dci_payroll;
use dci_payroll;

create table if not exists Address(
  addr_id int unsigned auto_increment not null,
  street_name varchar(100) not null,
  community varchar(100) not null,
  parish varchar(100) not null,
  primary key(addr_id)
);

create table if not exists Personnel(
  personnel_id int unsigned auto_increment not null,
  fname varchar(30) not null,
  lname varchar(30) not null,
  age int unsigned not null,
  address int unsigned not null,
  gender char not null,
  trn varchar(11),
  primary key(personnel_id),
  foreign key(address) references Address(addr_id) on delete cascade on update cascade
);

create table if not exists Task(
  task_id int unsigned auto_increment not null,
  task_name varchar(100) not null,
  primary key(task_id)
);

create table if not exists Personnel_Task(
  personnel_task_id int unsigned auto_increment not null,
  personnel_id int unsigned not null,
  task_id int unsigned not null,
  rate decimal(9,2) not null,
  primary key(personnel_task_id),
  foreign key(personnel_id) references Personnel(personnel_id) on delete cascade on update cascade,
  foreign key(task_id) references Task(task_id) on delete cascade on update cascade
);

create table if not exists Location(
  location_id int unsigned auto_increment not null,
  location_name varchar(100) not null,
  primary key(location_id)
);

create table if not exists Work_Done(
  work_id int unsigned auto_increment not null,
  personnel_task_id int unsigned not null,
  date_done date not null,
  period int unsigned not null,
  hrs_worked decimal(5,2) not null,
  location_id int unsigned not null,
  year int unsigned not null,
  primary key(work_id),
  foreign key(personnel_task_id) references Personnel_Task(personnel_task_id) on delete cascade on update cascade,
  foreign key(location_id) references Location(location_id) on delete cascade on update cascade
);

create table if not exists Pay(
  pay_id int unsigned auto_increment not null,
  personnel_id int unsigned not null,
  gross_amount decimal(9,2) not null,
  net_amount decimal(9,2) not null,
  period int unsigned not null,
  year int unsigned not null,
  primary key(pay_id),
  foreign key(personnel_id) references Personnel(personnel_id) on delete cascade on update cascade
);

create table if not exists Deduction(
  deduction_id int unsigned auto_increment not null,
  deduction_name varchar(100) not null,
  deduction_percentage decimal(5,2),
  fixed_amount decimal(9,2),
  periodic_deduction int not null,
  primary key(deduction_id)
);

create table if not exists Pay_Deduction(
  pay_deduction_id int unsigned auto_increment not null,
  deduction_id int unsigned not null,
  personnel_id int unsigned not null,
  duration int unsigned not null,
  primary key(pay_deduction_id),
  foreign key(deduction_id) references Deduction(deduction_id) on delete cascade on update cascade,
  foreign key(personnel_id) references Personnel(personnel_id) on delete cascade on update cascade
);

create table if not exists Pay_Details(
  pay_details_id int unsigned not null,
  personnel_id int unsigned not null,
  gross_amount decimal(9,2) not null,
  net_amount decimal(9,2) not null,
  period int unsigned not null,
  year int unsigned not null,
  description_of_work text not null,
  applied_deductions text not null,
  primary key(pay_details_id),
  foreign key(pay_details_id) references Pay(pay_id) on delete cascade on update cascade,
  foreign key(personnel_id) references Personnel(personnel_id) on delete cascade on update cascade
);


insert into address (street_name,community,parish)
  values ('1/2 No Head Lane','Trench Town','Kingston');

insert into address (street_name,community,parish)
  values ('7 Gospel Road','Hanna Town','Kingston');

insert into address (street_name,community,parish)
  values ('44 Dancehall Demio Drive','Dover','St. Mary');

insert into address (street_name,community,parish)
  values ('12 Warioland Terrance','Mushroom Kingdom','St. James');

/*-------------------------PERSONNEL-------------------------------------------*/
insert into personnel (fname,lname,age,address,gender,trn)
  values ('Ashley', 'Gordon',35,4,'F','123-456-789');

insert into personnel (fname,lname,age,address,gender,trn)
  values ('Roc', 'C',18,2,'M','123-456-789');

insert into personnel (fname,lname,age,address,gender,trn)
  values ('Rick', 'Law',25,1,'M','123-456-789');

insert into personnel (fname,lname,age,address,gender,trn)
  values ('Kenneth', 'Cole',22,3,'M','123-456-789');

insert into personnel (fname,lname,age,address,gender,trn)
  values ('Mario', 'Brown',27,4,'M','123-456-789');

/*---------------------------TASK---------------------------------------------*/
insert into task (task_name)
  values ('Guard');

insert into task (task_name)
  values ('Clean Up');

insert into task (task_name)
  values ('Filing');

insert into task (task_name)
  values ('Gardening');

insert into task (task_name)
  values ('Keep Inventory');

insert into task (task_name)
  values ('Designing');


/*---------------Personnel_Task-----------------------------------------------*/
insert into Personnel_Task (personnel_id,task_id,rate)
  values (1,3,200.00);

insert into Personnel_Task (personnel_id,task_id,rate)
  values (2,2,150.00);

insert into Personnel_Task (personnel_id,task_id,rate)
  values (3,1,330.00);

insert into Personnel_Task (personnel_id,task_id,rate)
  values (4,6,500.00);

insert into Personnel_Task (personnel_id,task_id,rate)
  values (5,4,340.00);

insert into Personnel_Task (personnel_id,task_id,rate)
  values (3,5,270.00);

/*----------Location----------------------*/
insert into Location (location_name)
  values ("Location 1");

insert into Location (location_name)
  values ("Location 2");

insert into Location (location_name)
  values ("Location 3");


/*-------------------------Work Done-------------------------------------------------*/
insert into Work_Done (personnel_task_id,date_done,period,hrs_worked,location_id,year)
  values (2,'2016-05-01',1,4,1,2016);

insert into Work_Done (personnel_task_id,date_done,period,hrs_worked,location_id,year)
  values (1,'2015-01-13',2,8,2,2015);


/*-----------------Deduction-----------------------*/
insert into deduction (deduction_name,deduction_percentage,periodic_deduction)
  values ('Income Tax',25,1);
insert into deduction (deduction_name,deduction_percentage,periodic_deduction)
  values ('Education Tax',3.5,1);
insert into deduction (deduction_name,deduction_percentage,periodic_deduction)
  values ('National Insurance Scheme Tax',2.5,1);
insert into deduction (deduction_name,deduction_percentage,periodic_deduction)
  values ('Human Employment and Resource Training Tax',3,1);
insert into deduction (deduction_name,deduction_percentage,periodic_deduction)
  values ('National Housing Trust Tax',3,1);
