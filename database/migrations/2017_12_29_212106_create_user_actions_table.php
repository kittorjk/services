<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->boolean('enl_ct');
            $table->boolean('enl_oc');
            $table->boolean('enl_prj');
            $table->boolean('enl_acv');
            $table->boolean('enl_adm');
            $table->boolean('ct_upl_fmt');
            $table->boolean('ct_vw_all');
            $table->boolean('ct_exp');
            $table->boolean('ct_edt');
            $table->boolean('oc_add');
            $table->boolean('oc_edt');
            $table->boolean('oc_apv_tech');
            $table->boolean('oc_apv_gg');
            $table->boolean('oc_nll');
            $table->boolean('oc_exp');
            $table->boolean('oc_prv_edt');
            $table->boolean('oc_prv_exp');
            $table->boolean('oc_ctf_add');
            $table->boolean('oc_ctf_del');
            $table->boolean('oc_ctf_edt');
            $table->boolean('oc_ctf_exp');
            $table->boolean('oc_inv_edt');
            $table->boolean('oc_inv_exp');
            $table->boolean('oc_inv_pmt');
            $table->boolean('prj_edt');
            $table->boolean('prj_exp');
            $table->boolean('prj_vw_eco');
            $table->boolean('prj_acc_rdr');
            $table->boolean('prj_acc_wty');
            $table->boolean('prj_bill_exp');
            $table->boolean('prj_asg_edt');
            $table->boolean('prj_asg_exp');
            $table->boolean('prj_evt_edt');
            $table->boolean('prj_di_edt');
            $table->boolean('prj_ctc_edt');
            $table->boolean('prj_st_edt');
            $table->boolean('prj_st_clr');
            $table->boolean('prj_st_del');
            $table->boolean('prj_st_exp');
            $table->boolean('prj_vtc_mod');
            $table->boolean('prj_vtc_edt');
            $table->boolean('prj_vtc_pmt');
            $table->boolean('prj_vtc_exp');
            $table->boolean('prj_vtc_rep');
            $table->boolean('prj_tk_edt');
            $table->boolean('prj_tk_clr');
            $table->boolean('prj_tk_del');
            $table->boolean('prj_tk_exp');
            $table->boolean('prj_acc_cat');
            $table->boolean('prj_cat_exp');
            $table->boolean('prj_act_edt');
            $table->boolean('prj_act_del');
            $table->boolean('prj_act_exp');
            $table->boolean('acv_vhc_req');
            $table->boolean('acv_vhc_edt');
            $table->boolean('acv_vhc_add');
            $table->boolean('acv_vhc_exp');
            $table->boolean('acv_vfr_add');
            $table->boolean('acv_vfr_mod');
            $table->boolean('acv_drv_upl_fmt');
            $table->boolean('acv_vhc_lic_mod');
            $table->boolean('acv_dvc_req');
            $table->boolean('acv_dvc_edt');
            $table->boolean('acv_dvc_add');
            $table->boolean('acv_dvc_exp');
            $table->boolean('acv_dfr_add');
            $table->boolean('acv_dfr_mod');
            $table->boolean('acv_cbr_mod');
            $table->boolean('acv_cbr_exp');
            $table->boolean('acv_mnt_add');
            $table->boolean('acv_mnt_edt');
            $table->boolean('acv_mnt_exp');
            $table->boolean('acv_ln_req');
            $table->boolean('acv_ln_edt');
            $table->boolean('acv_ln_add');
            $table->boolean('acv_ln_asg');
            $table->boolean('acv_ln_exp');
            $table->boolean('acc_adm');
            $table->boolean('adm_add_usr');
            $table->boolean('adm_acc_file');
            $table->boolean('adm_file_del');
            $table->boolean('adm_file_exp');
            $table->boolean('adm_acc_mail');
            $table->boolean('adm_acc_stf');
            $table->boolean('adm_acc_bch');
            $table->boolean('adm_bch_mod');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_actions');
    }
}
