<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    protected $fillable = ['user_id', 'enl_ct', 'enl_oc', 'enl_prj', 'enl_acv', 'enl_adm', 'ct_upl_fmt', 'ct_vw_all',
        'ct_exp', 'ct_edt', 'oc_add', 'oc_edt', 'oc_apv_tech', 'oc_apv_gg', 'oc_nll', 'oc_exp', 'oc_prv_edt',
        'oc_prv_exp', 'oc_ctf_add', 'oc_ctf_del', 'oc_ctf_edt', 'oc_ctf_exp', 'oc_inv_edt', 'oc_inv_exp', 'oc_inv_pmt',
        'prj_edt', 'prj_exp', 'prj_vw_eco', 'prj_acc_rdr', 'prj_acc_wty', 'prj_bill_exp', 'prj_asg_edt', 'prj_asg_exp',
        'prj_evt_edt', 'prj_di_edt', 'prj_ctc_edt', 'prj_st_edt', 'prj_st_clr', 'prj_st_del', 'prj_st_exp',
        'prj_vtc_mod', 'prj_vtc_edt', 'prj_vtc_pmt', 'prj_vtc_exp', 'prj_vtc_rep', 'prj_tk_edt', 'prj_tk_clr',
        'prj_tk_del', 'prj_tk_exp', 'prj_acc_cat', 'prj_cat_exp', 'prj_act_edt', 'prj_act_del', 'prj_act_exp',
        'acv_vhc_req', 'acv_vhc_edt', 'acv_vhc_add', 'acv_vhc_exp', 'acv_vfr_add', 'acv_vfr_mod', 'acv_drv_upl_fmt',
        'acv_vhc_lic_mod', 'acv_dvc_req', 'acv_dvc_edt', 'acv_dvc_add', 'acv_dvc_exp', 'acv_dfr_add', 'acv_dfr_mod',
        'acv_cbr_mod', 'acv_cbr_exp', 'acv_mnt_add', 'acv_mnt_edt', 'acv_mnt_exp', 'acv_ln_req', 'acv_ln_edt',
        'acv_ln_add', 'acv_ln_asg', 'acv_ln_exp', 'acc_adm', 'adm_add_usr', 'adm_acc_file', 'adm_file_del',
        'adm_file_exp', 'adm_acc_mail', 'adm_acc_stf', 'adm_acc_bch', 'adm_bch_mod', 'adm_emp_edt'];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
